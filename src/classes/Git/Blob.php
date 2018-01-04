<?php

namespace Classes\Git;

use Classes\Properties;

class Blob {
  use Properties;

  private $_repository;
  private $_tree;
  private $_path;
  private $_info = null;

  private $_commit = null;

  protected function get_repository() {
    return $this -> _repository;
  }

  protected function get_tree() {
    return $this -> _tree;
  }

  protected function get_path() {
    return $this -> _path;
  }

  protected function get_info() {
    if ($this -> _info === null) {
      $this -> _info = $this -> tree -> nodeInfo($this -> path);
    }
    return $this -> _info;
  }

  protected function get_commit() {
    if ($this -> _commit === null) {
      $hash = trim($this -> repository -> git -> execute([
        'rev-list', '-1', $this -> tree -> ref -> name, '--', $this -> path
      ]));
      $this -> _commit = $this -> repository -> commit($hash);
    }
    return $this -> _commit;
  }

  protected function get_type() {
    return $this -> info -> type;
  }

  protected function get_name() {
    return $this -> info -> name;
  }

  public function __construct(Repository $repository, Tree $tree, string $path) {
    $this -> _repository = $repository;
    $this -> _tree = $tree;
    $this -> _path = trim($path, '/');
  }

  public function matchesGlob(...$globs) {
    $globs = prepare_string_list($globs, '*');
    foreach ($globs as $glob) {
      if (matches_glob($this -> path, $glob)) {
        return true;
      }
    }
    return false;
  }

}
