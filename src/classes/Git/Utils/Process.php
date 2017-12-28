<?php

namespace Classes\Git\Utils;

class Process {

  static public function run($command, $args = null, $cwd = null, $displayOutput = false) {
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

    if ($process) {
      fclose($pipes[0]);

      $stdout = [];
      while (!feof($pipes[1])) {
        $buffer = stream_get_contents($pipes[1]);
        if ($displayOutput) {
          echo $buffer; flush();
        } else {
          $stdout[] = $buffer;
        }
      }
      fclose($pipes[1]);

      $stderr = [];
      while (!feof($pipes[2])) {
        $stderr[] = stream_get_contents($pipes[2]);
      }
      fclose($pipes[2]);

      $exitCode = proc_close($process);

      return [$exitCode, implode('', $stdout), implode('', $stderr)];
    }

    return [128, '', 'Failed to execute ' . $command];
  }

}
