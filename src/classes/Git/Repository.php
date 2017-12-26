<?php

namespace Classes\Git;

class Repository {

  private $config = [];
  private $path = '';
  /**
   * @var Commit[]
   */
  private $commits = [];
  /**
   * @var Branch[]
   */
  private $branches = null;
  /**
   * @var Branch
   */
  private $defaultBranch = null;

  public function __construct($path, $config) {
    $this -> config = $config;
    $this -> path = $path;
  }

  static public function getRepositories($config) {
    $result = [];

    list(, $names) = Process::run('find', '. -mindepth 2 -maxdepth 2 -type d -name *.git',
      $config -> repositoriesPath);

    $items = array_filter(array_map('trim', explode("\n", $names)), 'strlen');
    foreach ($items as $item) {
      if (preg_match('#([^/]+)/([^/]+)\.git$#', $item, $matches)) {
        list(, $group, $name) = $matches;
        $repository = new Repository($config -> repositoriesPath . '/' . $item, $config);
        @$result[$group][$name] = $repository;
      }
    }

    return $result;
  }

  static public function getRepository($name, $config) {
    $path = $config -> repositoriesPath . '/' . $name . '.git';
    return new Repository($path, $config);
  }

  public function exec(...$args) {
    if ((count($args) == 1) && is_array($args[0])) {
      $args = $args[0];
    }
    list($status, $stdout, $stderr) = Process::run(
      $this -> config -> gitBinary,
      $args,
      $this -> path
    );

    if ($status != 0) {
      throw new Exception($stderr);
    }
    return $stdout;
  }

  public function getCommit(string $hash) {
    $hash = trim($hash);
    if ($hash == '') return null;
    if (!array_key_exists($hash, $this -> commits)) {
      $this -> commits[$hash] = new Commit($this, $hash);
    }
    return $this -> commits[$hash];
  }

  public function getCommits(array $hashes) {
    return array_filter(array_map([$this, 'getCommit'], $hashes));
  }

  public function getCloneUrl() {
    $path = substr($this -> path, strlen($this -> config -> repositoriesPath) + 1);
    return str_replace('{path}', $path, $this -> config -> cloneOverSSHTemplate);
  }

  public function getBranches() {
    if ($this -> branches === null) {
      $this -> branches = [];
      $names = explode("\n", $this -> exec('branch', '--list'));
      foreach ($names as $name) {
        $name = explode(' ', trim($name));
        $isDefault = count($name) == 2;
        $name = array_last($name);
        if ($name != '') {
          $this -> branches[$name] = new Branch($this, $name);
          if ($isDefault) {
            $this -> defaultBranch = $this -> branches[$name];
          }
        }
      }
      if (!$this -> defaultBranch) {
        $this -> defaultBranch = array_first($this -> branches);
      }
    }
    return array_values($this -> branches);
  }

  public function getDefaultBranch() {
    $this -> getBranches();
    return $this -> defaultBranch;
  }

  public function getBranch($name) {
    $this -> getBranches();
    return array_key_exists($name, $this -> branches) ? $this -> branches[$name] : null;
  }

  public function getFiles($path = '') {
    return $this -> getDefaultBranch() -> getFiles($path);
  }

  public function create() {
    list($status) = Process::run(
      $this -> config -> gitBinary,
      ['init', '--bare', '--shared=0775', $this -> path]
    );
    return $status === 0;
  }

}
