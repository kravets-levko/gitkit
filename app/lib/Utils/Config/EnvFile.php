<?php

namespace Utils\Config;

class EnvFile extends Config {

  private function parse($str) {
    $lines = explode("\n", $str);
    $result = [];

    foreach ($lines as $line) {
      @list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      if ($name != '') {
        if ($value !== null) {
          $value = trim($value);
        }
        $result[$name] = $value;
      }
    }

    return $result;
  }

  public function __construct(string $filename) {
    $values = $this -> parse(file_get_contents($filename));
    if (!is_array($values)) $values = [];
    parent::__construct($values, true, '_');
  }

}
