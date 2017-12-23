<?php

namespace Classes\Git;

class Process {

  static public function run($command, $args = [], $cwd = null) {
    $command = trim(escapeshellarg($command) . ' ' .
      implode(' ', array_map('escapeshellarg', $args)));

    $process = proc_open($command, [
      0 => ['pipe', 'r'],
      1 => ['pipe', 'w'],
      2 => ['pipe', 'w'],
    ], $pipes, $cwd);

    while ($status = proc_get_status($process)) {
      if (!$status['running']) {
        break;
      }
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);

    return [$status['exitcode'], $stdout, $stderr];
  }

}
