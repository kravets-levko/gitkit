<?php

namespace Utils\Config;

class Environment extends Config {

  public function __construct(string $prefix = '') {
    $values = [];

    $prefix = $prefix != '' ? $prefix . '_' : '';
    $n = strlen($prefix);
    foreach ($_SERVER as $key => $value) {
      if (stripos($key, $prefix) === 0) {
        $values[substr($key, $n)] = $value;
      }
    }

    $values['https'] = isset($_SERVER['HTTPS']);

    parent::__construct($values, true, '_');
  }

}
