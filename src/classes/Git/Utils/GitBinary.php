<?php

namespace Classes\Git\Utils;

use Classes\Process\Binary;

class GitBinary extends Binary {

  public function execute($args) {
    list($exitCode, $stdout, $stderr) = parent::execute($args);
    if ($exitCode != 0) throw new GitException($stderr);
    return $stdout;
  }

  public function getOutputAsStream($args) {
    $process = $this -> start($args);
    $process -> stdin() -> close();
    $process -> stderr() -> close();
    return $process -> stdout();
  }

}
