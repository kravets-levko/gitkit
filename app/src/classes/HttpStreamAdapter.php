<?php

namespace Classes;

use Psr\Http\Message\StreamInterface;

class HttpStreamAdapter implements StreamInterface {

  private $_read;
  private $_eof;
  private $_close;

  public function __construct(callable $read, callable $eof, callable $close) {
    $this -> _read = $read;
    $this -> _eof = $eof;
    $this -> _close = $close;
  }

  public function __toString() {
    // Return remaining contents
    return $this -> getContents();
  }

  public function close() {
    if (is_callable($this -> _close)) {
      ($this -> _close)();
    }
    $this -> detach();
  }

  public function detach() {
    $this -> _read = null;
    $this -> _eof = null;
    $this -> _close = null;
    return null;
  }

  public function getSize() {
    return null; // size is unknown
  }

  public function tell() {
    throw new \RuntimeException('Operation is not supported');
  }

  public function eof() {
    if (is_callable($this -> _eof)) {
      return ($this -> _eof)();
    }
    return true;
  }

  public function isSeekable() {
    return false;
  }

  public function seek($offset, $whence = SEEK_SET) {
    throw new \RuntimeException('Operation is not supported');
  }

  public function rewind() {
    throw new \RuntimeException('Operation is not supported');
  }

  public function isWritable() {
    return false;
  }

  public function write($string) {
    throw new \RuntimeException('Operation is not supported');
  }

  public function isReadable() {
    return true;
  }

  public function read($length) {
    if (is_callable($this -> _read)) {
      return ($this -> _read)($length);
    }
    throw new \RuntimeException('Stream is closed');
  }

  public function getContents() {
    $result = '';
    while (!$this -> eof()) {
      $result .= $this -> read(-1);
    }
    return $result;
  }

  public function getMetadata($key = null) {
    return null; // no metadata
  }

}
