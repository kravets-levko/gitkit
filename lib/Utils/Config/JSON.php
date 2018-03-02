<?php

namespace Utils\Config;

class JSON extends Config {

  public function __construct(string $filename) {
    $values = json_decode(file_get_contents($filename), true);
    if (!is_array($values)) $values = [];
    parent::__construct($values);
  }

}
