<?php

namespace Classes\Git;

use Classes\Git\Utils\{ GitBinary, GitException, Parse };
use Classes\Process\Process;
use Classes\Properties;

class Repository {
  use Properties;

  /**
   * @var \stdClass
   */
  private $_config;
  private $_path;
  /**
   * @var Commit[]
   */
  private $_commits = [];

  protected function cached_git() {
    return new GitBinary($this -> _config -> gitBinary, $this -> _path);
  }

  protected function get_path() {
    return $this -> _path;
  }

  protected function cached_name() {
    $path = substr($this -> _path, strlen($this -> _config -> repositoriesRoot) + 1);
    return preg_replace('#\.git$#i', '', $path);
  }

  protected function cached_cloneUrls() {
    $path = substr($this -> _path, strlen($this -> _config -> repositoriesRoot) + 1);
    $https = $this -> _config -> https ? 'https' : 'http';
    $host = $this -> _config -> host;
    $user = $this -> _config -> gitUser;
    return [
      'ssh' => "{$user}@{$host}:{$path}",
      'http' => "{$https}://{$host}/{$path}",
    ];
  }

  protected function cached_branches() {
    list($names, $defaultName) = Parse::parseBranchList(
      $this -> git -> execute(['branch', '--list'])
    );

    return array_map(function($name) use ($defaultName) {
      $result = new Branch($this, $name);
      $result -> isDefault = $result -> name === $defaultName;
      return $result;
    }, $names);
  }

  protected function cached_defaultBranch() {
    $branches = $this -> branches;
    foreach ($branches as $branch) {
      if ($branch -> isDefault) return $branch;
    }
    return reset($branches);
  }

  protected function cached_tags() {
    $names = Parse::parseTagList($this -> git -> execute(['tag', '--list']));
    return array_map(function($name) {
      return new Tag($this, $name);
    }, $names);
  }

  protected function cached_latestCommit() {
    $hash = trim($this -> git -> execute(['rev-list', '-1', '--all']));
    return $this -> commit($hash);
  }

  protected function cached_info() {
    $result = @json_decode(file_get_contents($this -> _path . '.json'));
    return is_object($result) ? $result : (object)[];
  }

  public function __construct($path, $config) {
    $this -> _config = $config;
    $this -> _path = realpath($path);
  }

  public function commit(string $hash, bool $validate = false) {
    $hash = strtolower(trim($hash));
    if ($hash == '') return null;

    if ($validate) {
      try {
        $this -> git -> execute(['branch', '--contains', $hash]);
      } catch (GitException $e) {
        $hash = '';
      }
    }
    if ($hash == '') return null;

    if (!array_key_exists($hash, $this -> _commits)) {
      $this -> _commits[$hash] = new Commit($this, $hash);
    }
    return $this -> _commits[$hash];
  }

  public function commits(array $hashes) {
    $hashes = array_filter(array_map('trim', $hashes), 'strlen');
    return array_map([$this, 'commit'], $hashes);
  }

  public function branch($name) {
    $branches = $this -> branches;
    foreach ($branches as $branch) {
      if ($branch -> name == $name) return $branch;
    }
    return null;
  }

  public function tag($name) {
    $tags = $this -> tags;
    foreach ($tags as $tag) {
      if ($tag -> name == $name) return $tag;
    }
    return null;
  }

  public function ref($ref) {
    $result = $this -> branch($ref);
    if (!$result) $result = $this -> tag($ref);
    if (!$result) $result = $this -> commit($ref, true);
    return $result;
  }

  public function create() {
    $this -> git -> execute([
      'init', '--bare', '--shared=0775', $this -> _path
    ]);
  }

  static public function getRepositories($config) {
    $result = [];

    $process = new Process('find . -mindepth 2 -maxdepth 2 -type d -name *.git',
      $config -> repositoriesRoot);
    $names = $process -> stdout() -> read();
    $process -> close();

    $items = array_filter(array_map('trim', explode("\n", $names)), 'strlen');
    foreach ($items as $item) {
      if (preg_match('#([^/]+)/([^/]+)\.git$#', $item, $matches)) {
        list(, $group, $name) = $matches;
        $repository = new Repository($config -> repositoriesRoot . '/' . $item, $config);
        @$result[$group][$name] = $repository;
      }
    }

    return $result;
  }

  static public function getRepository($name, $config) {
    $path = $config -> repositoriesRoot . '/' . $name . '.git';
    if (!is_dir($path)) throw new GitException('Path does not exist');
    return new Repository($path, $config);
  }

}
