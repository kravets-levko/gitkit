<?php

namespace Classes\Git;

use Classes\Git\Utils\{ GitBinary, GitException, Parse };
use Classes\Process\Process;
use Classes\Properties;

class Repository {
  use Properties;

  /**
   * @var GitBinary
   */
  private $_git = null;

  /**
   * @var \stdClass
   */
  private $_config;
  private $_path;
  /**
   * @var Commit[]
   */
  private $_commits = [];
  /**
   * @var Branch[]
   */
  private $_branches = null;
  /**
   * @var Branch
   */
  private $_defaultBranch = null;

  /**
   * @var Tag[]
   */
  private $_tags = null;

  private $_latestCommit = null;

  private $_info = null;

  protected function get_git() {
    if ($this -> _git === null) {
      $this -> _git = new GitBinary($this -> _config -> gitBinary, $this -> _path);
    }
    return $this -> _git;
  }

  protected function get_path() {
    return $this -> _path;
  }

  protected function get_name() {
    $path = substr($this -> _path, strlen($this -> _config -> repositoriesRoot) + 1);
    return preg_replace('#\.git$#i', '', $path);
  }

  protected function get_cloneUrls() {
    $path = substr($this -> _path, strlen($this -> _config -> repositoriesRoot) + 1);
    $https = $this -> _config -> https ? 'https' : 'http';
    $host = $this -> _config -> host;
    $user = $this -> _config -> gitUser;
    return [
      'ssh' => "{$user}@{$host}:{$path}",
      'http' => "{$https}://{$host}/{$path}",
    ];
  }

  protected function get_branches() {
    if ($this -> _branches === null) {
      $this -> _branches = [];
      list($names, $defaultName) = Parse::parseBranchList(
        $this -> git -> execute(['branch', '--list'])
      );
      foreach ($names as $name) {
        $this -> _branches[$name] = new Branch($this, $name);
      }
      $this -> _defaultBranch = $defaultName !== null
        ? $this -> _branches[$defaultName]
        : array_first($this -> _branches);
    }
    return array_values($this -> _branches);
  }

  protected function get_defaultBranch() {
    $this -> get_branches();
    return $this -> _defaultBranch;
  }

  protected function get_tags() {
    if ($this -> _tags === null) {
      $this -> _tags = [];
      $names = Parse::parseTagList($this -> git -> execute(['tag', '--list']));
      foreach ($names as $name) {
        $this -> _tags[$name] = new Tag($this, $name);
      }
    }
    return array_values($this -> _tags);
  }

  protected function get_latestCommit() {
    if ($this -> _latestCommit === null) {
      $hash = trim($this -> git -> execute(['rev-list', '-1', '--all']));
      $this -> _latestCommit = $this -> commit($hash);
    }
    return $this -> _latestCommit;
  }

  protected function get_info() {
    if ($this -> _info === null) {
      $this -> _info = @json_decode(file_get_contents($this -> _path . '.json'));
      if (!is_object($this -> _info)) {
        $this -> _info = (object)[];
      }
    }
    return $this -> _info;
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
    $this -> get_branches();
    return array_key_exists($name, $this -> _branches) ? $this -> _branches[$name] : null;
  }

  public function tag($name) {
    $this -> get_tags();
    return array_key_exists($name, $this -> _tags) ? $this -> _tags[$name] : null;
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
