<?php

namespace Utils\Config;

class Environment extends \StdClass {

  public function __construct($prefix = 'gitkit') {
    foreach ($_SERVER as $key => $value) {
      $key = explode('_', strtolower($key));
      if (reset($key) == $prefix) {
        array_shift($key);
        $key = lcfirst(implode('', array_map('ucfirst', $key)));
        $this -> {$key} = $value;
      }

      $this -> https = isset($_SERVER['HTTPS']);
      $this -> host = $_SERVER['SERVER_NAME'];
    }
  }

}
