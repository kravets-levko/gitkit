<?php

namespace SSH;

use Process\Binary;
use Utils\Mixin\Cached;

class Context {
  use Cached;

  public $config;
  public $keygen;

  public function __construct(mixed $config) {
    $this -> config = $config;
    $this -> keygen = new Binary($this -> config -> keygenBinary);
  }

}
