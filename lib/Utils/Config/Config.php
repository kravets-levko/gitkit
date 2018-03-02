<?php

namespace Utils\Config;

class Config extends \StdClass {

  public function __construct(array $values = []) {
    foreach ($values as $key => $value) {
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
