<?php

namespace Git;

use Utils\Mixin\{ Properties, Cached };

class Repository {
  use Properties;
  use Cached;

  protected $context;
  protected $_path;
  protected $_defaultBranchName;

  protected function get_path() {
    return $this -> _path;
  }

  protected function get_name() {
    return $this -> cached(__METHOD__, function() {
      $path = substr($this -> path,
        strlen($this -> context -> config -> repositoriesRoot) + 1);
      return preg_replace('#\.git$#i', '', $path);
    });
  }

  protected function get_description() {
    return $this -> cached('description', function() {
      return @file_get_contents($this -> path . '/description');
    });
  }

  protected function set_description(string $value) {
    @file_put_contents($this -> path . '/description', $value);
    $this -> cachedUnset('description');
  }

  protected function get_cloneUrls() {
    $config = $this -> context -> config;
    $path = substr($this -> path, strlen($config -> repositoriesRoot) + 1);
    $https = $config -> https ? 'https' : 'http';
    $host = $config -> host;
    $user = $config -> gitUser;
    return [
      'ssh' => "{$user}@{$host}:{$path}",
      'http' => "{$https}://{$host}/{$path}",
    ];
  }

  protected function get_latestCommit() {
    return $this -> cached(__METHOD__, function() {
      $hash = trim($this -> context -> execute(['rev-list', '-1', '--all']));
      return $this -> context -> commit($hash);
    });
  }

  protected function get_branches() {
    return $this -> cached(__METHOD__, function() {
      list($names, $defaultName) = $this -> context -> parseBranchList(
        $this -> context -> execute(['branch', '--list'])
      );
      $this -> _defaultBranchName = $defaultName;

      $result = [];
      foreach ($names as $name) {
        $result[$name] = $this -> context -> branch($name);
      }
      return $result;
    });
  }

  protected function get_defaultBranch() {
    $branches = $this -> branches;
    return $branches[$this -> _defaultBranchName];
  }

  protected function get_tags() {
    return $this -> cached(__METHOD__, function() {
      $names = $this -> context -> parseTagList(
        $this -> context -> execute(['tag', '--list'])
      );
      $result = [];
      foreach ($names as $name) {
        $result[$name] = $this -> context -> tag($name);
      }
      return $result;
    });
  }

  public function __construct($config, string $path) {
    $this -> _path = realpath($path);
    $this -> context = new RepositoryContext($config, $path);
  }

  public function branch($name) {
    $branches = $this -> branches;
    return array_key_exists($name, $branches) ? $branches[$name] : null;
  }

  public function tag($name) {
    $tags = $this -> tags;
    return array_key_exists($name, $tags) ? $tags[$name] : null;
  }

  public function commit($hash) {
    return $this -> context -> commit($hash, true);
  }

  public function ref($ref) {
    $result = $this -> branch($ref);
    if (!$result) $result = $this -> tag($ref);
    if (!$result) $result = $this -> commit($ref);
    return $result;
  }

  public function init() {
    $this -> git -> execute([
      'init', '--bare', '--shared=0775', $this -> _path
    ]);
  }

}
