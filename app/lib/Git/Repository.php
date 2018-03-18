<?php

namespace Git;

use Utils\Mixin\{ Properties, Cached };
use Process\Process;

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
    return $this -> cached('name', function() {
      $path = substr($this -> path,
        strlen($this -> context -> config -> repositoriesRoot) + 1);
      return preg_replace('#\.git$#i', '', $path);
    });
  }

  protected function set_name($value) {
    if ($value != '') {
      $path = explode('/', $this -> _path);
      $oldName = array_pop($path);
      if ($value != $oldName) {
        $path[] = $value . '.git';
        $path = implode('/', $path);

        rename($this -> _path, $path);
        $this -> _path = $path;
        $this -> context -> git -> cwd = $path;
        $this -> cachedUnset('name');
      }
    }
  }

  protected function get_description() {
    return $this -> cached('description', function() {
      return @file_get_contents($this -> path . '/description');
    });
  }

  protected function set_description(string $value) {
    @file_put_contents($this -> path . '/description', $value);
    $this -> cachedSet('description', $value);
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

  protected function get_isEmpty() {
    return $this -> cached(__METHOD__, function() {
      // Try to find any commit
      list($exitCode, $stdout, $stderr) = $this -> context -> git -> execute([
        'rev-list', '-1', '--all'
      ]);
      // When running from terminal, `git-rev-list` exists with non-zero code.
      // But here it exists with zero code, but empty output. Weird
      return ($exitCode != 0) || (trim($stdout) == '') || (trim($stderr) != '');
    });
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

  protected function set_defaultBranch(Branch $branch) {
    if ($branch -> name != $this -> _defaultBranchName) {
      if (array_key_exists($branch -> name, $this -> branches)) {
        $this -> context -> execute([
          'symbolic-ref', 'HEAD', 'refs/heads/' . $branch -> name
        ]);
        $this -> _defaultBranchName = $branch -> name;
      }
    }
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
    // TODO: If path does not exist - it should be possible to call `init()` method
    $this -> _path = validate_path($path, $config -> repositoriesRoot);
    $this -> context = new RepositoryContext($config, $this -> _path);
  }

  /**
   * @param string $name
   * @return Branch | null
   */
  public function branch($name) {
    $branches = $this -> branches;
    return array_key_exists($name, $branches) ? $branches[$name] : null;
  }

  /**
   * @param string $name
   * @return Tag | null
   */
  public function tag($name) {
    $tags = $this -> tags;
    return array_key_exists($name, $tags) ? $tags[$name] : null;
  }

  /**
   * @param string $hash
   * @return Commit | null
   */
  public function commit($hash) {
    return $this -> context -> commit($hash, true);
  }

  public function ref($ref): Ref {
    $result = $this -> branch($ref);
    if (!$result) $result = $this -> tag($ref);
    if (!$result) $result = $this -> commit($ref);
    return $result;
  }

  public function delete() {
    $path = validate_path($this -> path, $this -> context -> config -> repositoriesRoot);
    if ($path) {
      $process = new Process(Process::prepareCommand('rm', ['-rf', $path]));
      $process -> stdout() -> close();
      $stderr = $process -> stderr() -> read();
      $exitCode = $process -> close();
      if ($exitCode != 0) {
        throw new Exception($stderr);
      }
    }
  }

}
