<?php

namespace Utils\Mixin;

trait Cached {

  private $__cache = [];

  protected function cached($key, callable $getter) {
    $key = serialize($key);
    if (!array_key_exists($key, $this -> __cache)) {
      $this -> __cache[$key] = $getter();
    }
    return $this -> __cache[$key];
  }

  protected function cachedIsset($key) {
    $key = serialize($key);
    return array_key_exists($key, $this -> __cache);
  }

  protected function cachedGet($key, $default = null) {
    $key = serialize($key);
    return array_key_exists($key, $this -> __cache) ? $this -> __cache[$key] : $default;
  }

  protected function cachedSet($key, $value) {
    $key = serialize($key);
    $this -> __cache[$key] = $value;
  }

  protected function cachedUnset($key) {
    $key = serialize($key);
    unset($this -> __cache[$key]);
  }

}
