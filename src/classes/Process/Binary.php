<?php

namespace Classes\Process;

class Binary {

  public $filename = null;
  public $cwd = null;
  public $env = null;

  public function __construct(string $filename, $cwd = null, $env = null) {
    $this -> filename = $filename;
    $this -> cwd = $cwd;
    $this -> env = $env;
  }

  public function start($args, $descriptors = []): Process {
    return new Process(
      Process::prepareCommand($this -> filename, $args),
      $this -> cwd,
      $this -> env,
      $descriptors
    );
  }

  public function execute($args) {
    $process = $this -> start($args);
    $stdout = $process -> stdout() -> read();
    $stderr = $process -> stderr() -> read();
    $exitCode = $process -> close();
    return [$exitCode, $stdout, $stderr];
  }

}
