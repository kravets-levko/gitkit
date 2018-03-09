<?php

namespace Process;

class StdPipe {

  const STDIN = 0;
  const STDOUT = 1;
  const STDERR = 2;

  /**
   * @var Process
   */
  protected $process = null;

  protected $handle = null;
  protected $ownHandle = true;

  public function __construct($handle, $ownHandle = true) {
    $this -> handle = is_resource($handle) ? $handle : null;
    $this -> ownHandle = $ownHandle;
  }

  public function handle() {
    return $this -> handle;
  }

  public function attach(Process $process) {
    if (
      ($this === $process -> stdin()) ||
      ($this === $process -> stdout()) ||
      ($this === $process -> stderr())
    ) {
      $this -> process = $process;
    } else {
      trigger_error('Pipe does not belong to process', E_USER_WARNING);
    }
  }

  public function type() {
    if ($this -> process) {
      if ($this === $this -> process -> stdin()) return StdPipe::STDIN;
      if ($this === $this -> process -> stdout()) return StdPipe::STDOUT;
      if ($this === $this -> process -> stderr()) return StdPipe::STDERR;
    }
    return null;
  }

  public function read($length = -1) {
    if (!$this -> handle) return false;
    if ($length < 0) {
      // Read all and close pipe
      $result = [];
      while (!feof($this -> handle)) {
        $buffer = fread($this -> handle, 1000);
        if ($buffer === false) break;
        if ($buffer != '') $result[] = $buffer;
      }
      $this -> close();
      return implode('', $result);
    } else {
      return fread($this -> handle, $length);
    }
  }

  public function write($data) {
    if ($this -> handle) {
      fwrite($this -> handle, $data);
    }
  }

  public function eof() {
    return $this -> handle ? feof($this -> handle) : true;
  }

  public function close() {
    if ($this -> handle && $this -> ownHandle) {
      fclose($this -> handle);
    }
    $this -> handle = null;
    $this -> process = null;
  }

}
