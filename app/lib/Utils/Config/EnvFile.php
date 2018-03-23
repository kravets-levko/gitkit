<?php

namespace Utils\Config;

use System\EnvFile as EnvFileParser;

class EnvFile extends Config {

  public function __construct(string $filename) {
    $env = new EnvFileParser($filename);
    parent::__construct($env -> variables(), true, '_');
  }

}
