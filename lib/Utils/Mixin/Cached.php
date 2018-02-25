<?php

namespace Utils\Mixin;

trait Cached {

  private $__cache = [];

  protected function cached(mixed $key, callable $getter) {
    $key = serialize($key);
    if (!array_key_exists($key, $this -> __cache)) {
      $this -> __cache[$key] = $getter();
    }
    return $this -> __cache[$key];
  }

  protected function cachedIsset(mixed $key) {
    $key = serialize($key);
    return array_key_exists($key, $this -> __cache);
  }

  protected function cachedGet(mixed $key, mixed $default = null) {
    $key = serialize($key);
    return array_key_exists($key, $this -> __cache) ? $this -> __cache[$key] : $default;
  }

  protected function cachedSet(mixed $key, mixed $value) {
    $key = serialize($key);
    $this -> __cache[$key] = $value;
  }

  protected function cachedUnset(mixed $key) {
    $key = serialize($key);
    unset($this -> __cache[$key]);
  }

}
