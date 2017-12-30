<?php

namespace Classes\Process;

class Process {

  protected $handle = null;

  /**
   * @var StdPipe
   */
  protected $stdin = null;
  /**
   * @var StdPipe
   */
  protected $stdout = null;
  /**
   * @var StdPipe
   */
  protected $stderr = null;

  protected $exitCode = null;

  protected $status = null;

  static public function prepareCommand($command, $args) {
    if (is_array($args)) {
      $args = array_map('strval', $args);
      $args = array_map('escapeshellarg', $args);
      $args = implode(' ', $args);
    }
    if (!is_string($args)) $args = '';

    $command = escapeshellarg($command);

    return $command . ($args == '' ? '' : ' ') . $args;
  }

  public function __construct($command, $cwd = null, $env = null, $descriptors = []) {
    if (!is_array($descriptors)) $descriptors = [];
    if (!isset($descriptors[0])) $descriptors[0] = ['pipe', 'r'];
    if (!isset($descriptors[1])) $descriptors[1] = ['pipe', 'w'];
    if (!isset($descriptors[2])) $descriptors[2] = ['pipe', 'w'];

    $descriptors = [
      0 => $descriptors[0],
      1 => $descriptors[1],
      2 => $descriptors[2],
    ];
    $ownHandle = [
      0 => true,
      1 => true,
      2 => true,
    ];

    foreach ($descriptors as $index => $descriptor) {
      if ($descriptor instanceof StdPipe) {
        $descriptors[$index] = $descriptor -> handle();
      }
      $ownHandle[$index] = !is_resource($descriptors[$index]);
    }

    $this -> handle = proc_open($command, $descriptors, $pipes, $cwd, $env, [
      'bypass_shell' => true,
    ]);
    if (is_resource($this -> handle)) {
      $this -> stdin = new StdPipe($pipes[0], $ownHandle[0]);
      $this -> stdout = new StdPipe($pipes[1], $ownHandle[1]);
      $this -> stderr = new StdPipe($pipes[2], $ownHandle[2]);

      $this -> stdin -> attach($this);
      $this -> stdout -> attach($this);
      $this -> stderr -> attach($this);
    } else {
      $this -> handle = null;
    }
  }

  public function __destruct() {
    $this -> close();
  }

  public function stdin() {
    return $this -> stdin;
  }
  public function stdout() {
    return $this -> stdout;
  }
  public function stderr() {
    return $this -> stderr;
  }

  public function status() {
    if (is_resource($this -> handle)) {
      if ($this -> status === null) {
        $this -> status = proc_get_status($this -> handle);
      } elseif (!$this -> status['running']) {
        $this -> status = proc_get_status($this -> handle);
      }
      return $this -> status;
    } else {
      return null;
    }
  }

  public function close() {
    if ($this -> stdin) $this -> stdin -> close();
    if ($this -> stdout) $this -> stdout -> close();
    if ($this -> stderr) $this -> stderr -> close();
    if ($this -> handle) {
      $this -> exitCode = proc_close($this -> handle);
      $this -> handle = null;
    }
    return $this -> exitCode;
  }

}
