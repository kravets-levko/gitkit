<?php

namespace Classes\Process;

class Binary {

  private $filename = null;

  public function __construct(string $filename) {
    $this -> filename = $filename;
  }

  public function start($args, $cwd = null, $env = null, $descriptors = []): Process {
    return new Process(
      Process::prepareCommand($this -> filename, $args),
      $cwd,
      $env,
      $descriptors
    );
  }

  public function execute($args, $cwd = null, $env = null) {
    $process = $this -> start($args, [], $cwd, $env);
    $stdout = $process -> stdout() -> read();
    $stderr = $process -> stderr() -> read();
    $exitCode = $process -> close();
    return [$exitCode, $stdout, $stderr];
  }

}
