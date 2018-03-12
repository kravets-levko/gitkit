<?php

namespace Git;

use Process\Binary;

class RepositoryContext {

  protected $commits = [];
  protected $branches = [];
  protected $tags = [];

  public $config;
  public $git;

  public function __construct($config, string $cwd) {
    $this -> config = $config;
    $this -> git = new Binary($this -> config -> gitBinary, $cwd);
  }

  public function execute($args, $returnAsStream = false) {
    if ($returnAsStream) {
      $process = $this -> git -> start($args);
      $process -> stdin() -> close();
      $process -> stderr() -> close();
      return $process -> stdout();
    } else {
      list($exitCode, $stdout, $stderr) = $this -> git -> execute($args);
      if ($exitCode != 0) throw new Exception($stderr);
      return $stdout;
    }
  }

  public function parseBranchList(string $str) {
    $default = null;
    $branches = [];

    $names = explode("\n", $str);
    foreach ($names as $name) {
      $name = explode(' ', trim($name));
      $isDefault = count($name) > 1;
      $name = end($name);
      if ($name != '') {
        $branches[] = $name;
        if ($isDefault && ($default === null)) $default = $name;
      }
    }

    sort($branches);

    if (!in_array($default, $branches) && (count($branches) > 0)) {
      $default = $branches[0];
    }

    return [$branches, $default];
  }

  public function parseTagList(string $str) {
    $names = explode("\n", $str);
    $names = array_filter(array_map('trim', $names), 'strlen');
    ksort($names);
    return array_reverse($names);
  }

  public function commit(string $hash, bool $validate = false) {
    $hash = strtolower(trim($hash));
    if ($hash == '') return null;

    if ($validate) {
      try {
        // TODO: use rev-list
        $this -> git -> execute(['branch', '--contains', $hash]);
      } catch (Exception $e) {
        $hash = '';
      }
    }
    if ($hash == '') return null;

    if (!array_key_exists($hash, $this -> commits)) {
      $this -> commits[$hash] = new Commit($this, $hash);
    }
    return $this -> commits[$hash];
  }

  public function commits(array $hashes) {
    $hashes = array_filter(array_map('trim', $hashes), 'strlen');
    return array_map([$this, 'commit'], $hashes);
  }

  public function branch($name) {
    if (!array_key_exists($name, $this -> branches)) {
      $this -> branches[$name] = new Branch($this, $name);
    }
    return $this -> branches[$name];
  }

  public function tag($name) {
    if (!array_key_exists($name, $this -> tags)) {
      $this -> tags[$name] = new Tag($this, $name);
    }
    return $this -> tags[$name];
  }

}
