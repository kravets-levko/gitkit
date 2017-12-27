<?php

namespace Classes\Git;

use \Classes\Git\Utils\Config;
use \Classes\Git\Utils\LazyArray;
use \Classes\Git\Utils\Process;
use \Classes\Git\Core\Commit;
use \Classes\Git\Core\Branch;
use \Classes\Git\Core\Tag;

class Repository {

  /**
   * @var Config
   */
  private $config;
  private $path;
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

  /**
   * @var Tag[]
   */
  private $tags = null;

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
    $hashes = array_filter(array_map('trim', $hashes), 'strlen');
    return new LazyArray($hashes, [$this, 'getCommit']);
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

  public function getTags() {
    if ($this -> tags === null) {
      $this -> tags = [];
      $names = explode("\n", $this -> exec('tag', '--list'));
      foreach ($names as $name) {
        $this -> tags[$name] = new Tag($this, $name);
      }
    }
    return array_values($this -> tags);
  }

  public function getTag($name) {
    $this -> getTags();
    return array_key_exists($name, $this -> tags) ? $this -> tags[$name] : null;
  }

  public function getRef($ref) {
    $result = $this -> getBranch($ref);
    if (!$result) $result = $this -> getTag($ref);
    // TODO: Ref may be a commit - it should exist in this repo
    return $result;
  }

  public function create() {
    list($status) = Process::run(
      $this -> config -> gitBinary,
      ['init', '--bare', '--shared=0775', $this -> path]
    );
    return $status === 0;
  }

}
