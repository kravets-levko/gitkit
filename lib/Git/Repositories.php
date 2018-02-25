<?php

namespace Git;

use Process\Process;

class Repositories {

  protected $config;
  protected $repositories = [];

  public function __construct(mixed $config) {
    $this -> config = $config;
  }

  public function getRepository($path) {
    if (!array_key_exists($path, $this -> repositories)) {
      $this -> repositories[$path] = new Repository($this -> config, $path);
    }
    return $this -> repositories[$path];
  }

  public function getRepositories() {
    $result = [];

    $process = new Process('find . -mindepth 2 -maxdepth 2 -type d -name *.git',
      $this -> config -> repositoriesRoot);
    $names = $process -> stdout() -> read();
    $process -> close();

    $items = array_filter(array_map('trim', explode("\n", $names)), 'strlen');
    foreach ($items as $item) {
      $path = $this -> config -> repositoriesRoot . '/' . $item;
      $result[] = $this -> getRepository($path);
    }

    return $result;
  }

}
