<?php

namespace Utils\Config;

class Environment extends Config {

  public function __construct(string $prefix = '') {
    $values = [];

    foreach ($_SERVER as $key => $value) {
      $key = explode('_', strtolower($key));
      if (($prefix == '') || (reset($key) == $prefix)) {
        array_shift($key);
        $key = lcfirst(implode('', array_map('ucfirst', $key)));
        $values[$key] = $value;
      }
    }

    $values['https'] = isset($_SERVER['HTTPS']);
    $values['host'] = $_SERVER['SERVER_NAME'];

    parent::__construct($values);
  }

}
