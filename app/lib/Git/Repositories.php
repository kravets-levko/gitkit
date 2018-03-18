<?php

namespace Git;

use Process\{ Process, Binary };

class Repositories {

  protected $config;
  protected $repositories = [];

  public function __construct($config) {
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

  public function createRepository($path) {
    $git = new Binary($this -> config -> gitBinary, $path);
    list($exitCode, , $stderr) = $git -> execute([
      'init', '--bare', '--shared=0775', $path
    ]);
    if ($exitCode != 0) throw new Exception($stderr);
    $repositoryPath = realpath($path);
    if (!$repositoryPath) {
      throw new Exception("Cannot create repository at '${path}'");
    }
    return $this -> getRepository($repositoryPath);
  }

}
