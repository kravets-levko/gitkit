<?php

namespace Classes\Git;

class Process {

  static public function run($command, $args = null, $cwd = null) {
    if (is_array($args)) {
      $args = implode(' ', array_map('escapeshellarg', $args));
    }
    if (is_string($args) && ($args != '')) {
      $args = ' ' . $args;
    } else {
      $args = '';
    }

    $command = trim(escapeshellarg($command) . $args);

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
