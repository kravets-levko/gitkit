<?php

namespace Classes\Git\Core;

use \Classes\Git\Utils\Properties;
use \Classes\Git\Repository;

/**
 * Class Blob
 *
 * @property-read Repository $repository
 * @property-read Tree $tree
 * @property-read string $path
 * @property-read \stdClass $info
 * @property-read Commit $commit
 * @property-read string $type
 * @property-read string $name
 */
class Blob {
  use Properties;

  private $_repository;
  private $_tree;
  private $_path;
  private $_info = null;

  private $commit = null;

  public function __construct(Repository $repository, Tree $tree, string $path) {
    $this -> _repository = $repository;
    $this -> _tree = $tree;
    $this -> _path = trim($path, '/');
  }

  protected function getRepository() {
    return $this -> _repository;
  }

  protected function getTree() {
    return $this -> _tree;
  }

  protected function getPath() {
    return $this -> _path;
  }

  protected function getInfo() {
    if ($this -> _info === null) {
      $this -> _info = $this -> tree -> nodeInfo($this -> path);
    }
    return $this -> _info;
  }

  protected function getCommit() {
    if ($this -> commit === null) {
      $hash = trim($this -> repository -> exec(
        'rev-list', '-1', $this -> tree -> ref -> name, '--', $this -> path
      ));
      $this -> commit = $this -> repository -> commit($hash);
    }
    return $this -> commit;
  }

  protected function getType() {
    return $this -> info -> type;
  }

  protected function getName() {
    return $this -> info -> name;
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
