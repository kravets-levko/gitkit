<?php

namespace Classes\Git\Utils;

class LazyArray implements \Countable, \Iterator, \ArrayAccess {

  private $keys = [];
  private $position = 0;
  private $fetch = null;

  private function fetch($key) {
    return $this -> fetch !== null ? ($this -> fetch)($key) : null;
  }

  public function __construct(array $keys, callable $fetch) {
    $this -> keys = array_values($keys);
    $this -> fetch = $fetch;
  }

  public function count() {
    return count($this -> keys);
  }

  public function offsetExists($offset) {
    return in_array($offset, $this -> keys);
  }

  public function offsetGet($offset) {
    return $this -> fetch($offset);
  }

  public function offsetSet($offset, $value) {
  }

  public function offsetUnset($offset) {
  }

  public function current() {
    return $this -> fetch($this -> key());
  }

  public function next() {
    if ($this -> valid()) {
      $this -> position += 1;
    }
  }

  public function key() {
    return $this -> valid() ? $this -> keys[$this -> position] : null;
  }

  public function valid() {
    return $this -> position < count($this -> keys);
  }

  public function rewind() {
    $this -> position = 0;
  }

}
