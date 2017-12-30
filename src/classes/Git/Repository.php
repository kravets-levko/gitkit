<?php

namespace Classes\Git;

use \Classes\Git\Utils\Process;
use \Classes\Git\Utils\Properties;
use \Classes\Git\Core\Commit;
use \Classes\Git\Core\Branch;
use \Classes\Git\Core\Tag;

use \Classes\Process\Binary;

/**
 * @property-read string $cloneUrl
 * @property-read Branch[] $branches
 * @property-read Branch $defaultBranch
 * @property-read Tag[] $tags
 * @property-read Commit $latestCommit
 * @property-read \StdClass $info
 */
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

  private $git = null;

  private $_info = null;

  public function __construct($path, $config) {
    $this -> _config = $config;
    $this -> _path = $path;
    $this -> git = new Binary($this -> _config -> gitBinary);
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
    if (!is_dir($path)) throw new Exception('Path does not exist');
    return new Repository($path, $config);
  }

  protected function getCloneUrls() {
    $path = substr($this -> _path, strlen($this -> _config -> repositoriesPath) + 1);
    $result = [];
    foreach ($this -> _config -> cloneUrlTemplates as $key => $template) {
      $result[$key] = str_replace('{path}', $path, $template);
    }
    return $result;
  }

  protected function getBranches() {
    if ($this -> _branches === null) {
      $this -> _branches = [];
      $names = explode("\n", $this -> exec('branch', '--list'));
      foreach ($names as $name) {
        $name = explode(' ', trim($name));
        $isDefault = count($name) > 1;
        $name = array_last($name);
        if ($name != '') {
          $this -> _branches[$name] = new Branch($this, $name);
          if ($isDefault) {
            $this -> _defaultBranch = $this -> _branches[$name];
          }
        }
      }
      if (!$this -> _defaultBranch) {
        $this -> _defaultBranch = array_first($this -> _branches);
      }

      ksort($this -> _branches);
    }
    return array_values($this -> _branches);
  }

  protected function getDefaultBranch() {
    $this -> getBranches();
    return $this -> _defaultBranch;
  }

  protected function getTags() {
    if ($this -> _tags === null) {
      $this -> _tags = [];
      $names = explode("\n", $this -> exec('tag', '--list'));
      $names = array_filter(array_map('trim', $names), 'strlen');
      foreach ($names as $name) {
        $this -> _tags[$name] = new Tag($this, $name);
      }

      ksort($this -> _tags);
      $this -> _tags = array_reverse($this -> _tags);
    }
    return array_values($this -> _tags);
  }

  protected function getLatestCommit() {
    if ($this -> _latestCommit === null) {
      $hash = trim($this -> exec(['rev-list', '-1', '--all']));
      $this -> _latestCommit = $this -> commit($hash);
    }
    return $this -> _latestCommit;
  }

  protected function getInfo() {
    if ($this -> _info === null) {
      $this -> _info = @json_decode(file_get_contents($this -> _path . '.json'));
      if (!is_object($this -> _info)) {
        $this -> _info = (object)[];
      }
    }
    return $this -> _info;
  }

  public function exec(...$args) {
    if ((count($args) == 1) && is_array($args[0])) {
      $args = $args[0];
    }
    list($status, $stdout, $stderr) = Process::run(
      $this -> _config -> gitBinary,
      $args,
      $this -> _path
    );

    if ($status != 0) {
      throw new Exception($stderr);
    }
    return $stdout;
  }

  public function passthru(...$args) {
    if ((count($args) == 1) && is_array($args[0])) {
      $args = $args[0];
    }
    list($status, , $stderr) = Process::run(
      $this -> _config -> gitBinary,
      $args,
      $this -> _path,
      true
    );

    if ($status != 0) {
      throw new Exception($stderr);
    }
  }

  public function commit(string $hash, bool $validate = false) {
    $hash = trim($hash);
    if ($hash == '') return null;

    if ($validate) {
      try {
        $this -> exec('branch', '--contains', $hash);
      } catch (Exception $e) {
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
    $this -> getBranches();
    return array_key_exists($name, $this -> _branches) ? $this -> _branches[$name] : null;
  }

  public function tag($name) {
    $this -> getTags();
    return array_key_exists($name, $this -> _tags) ? $this -> _tags[$name] : null;
  }

  public function ref($ref) {
    $result = $this -> branch($ref);
    if (!$result) $result = $this -> tag($ref);
    if (!$result) $result = $this -> commit($ref, true);
    return $result;
  }

  public function create() {
    list($status) = Process::run(
      $this -> _config -> gitBinary,
      ['init', '--bare', '--shared=0775', $this -> _path]
    );
    return $status === 0;
  }

}
