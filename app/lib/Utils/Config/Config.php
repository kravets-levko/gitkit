<?php

namespace Utils\Config;

class Config extends \StdClass {

  protected function transformKey(string $key, string $delimiter = '_'): string {
    return lcfirst(implode('', array_map('ucfirst', explode($delimiter, strtolower($key)))));
  }

  public function __construct(array $values = [], $transformKeys = false, $delimiter = '_') {
    foreach ($values as $key => $value) {
      if ($transformKeys) $key = $this -> transformKey($key, $delimiter);
      $this -> {$key} = $value;
    }
  }

  public function extendWith($config) {
    if ($config instanceof Config) {
      $config = $config -> __toArray();
    } elseif ($config instanceof \StdClass) {
      $config = (array)$config;
    }
    if (is_array($config)) {
      foreach ($config as $key => $value) {
        $this ->{$key} = $value;
      }
    }
    return $this;
  }

  public function __toArray() {
    return (array)$this;
  }

}
