<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

class Blob {

  private $repository;
  private $tree;
  private $path;
  private $info = null;

  private $commit = null;

  public function __construct(Repository $repository, Tree $tree, string $path) {
    $this -> repository = $repository;
    $this -> tree = $tree;
    $this -> path = trim($path, '/');
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getTree() {
    return $this -> tree;
  }

  public function getCommit() {
    if ($this -> commit === null) {
      $hash = trim($this -> getRepository() -> exec(
        'rev-list', '-1', $this -> getTree() -> getRef() -> getRef(), '--', $this -> getPath()
      ));
      $this -> commit = $this -> getRepository() -> getCommit($hash);
    }
    return $this -> commit;
  }

  public function getPath() {
    return $this -> path;
  }

  public function getInfo() {
    if ($this -> info === null) {
      $this -> info = $this -> getTree() -> getNodeInfo($this -> getPath());
    }
    return $this -> info;
  }

  public function getType() {
    return $this -> getInfo() -> type;
  }

  public function getName() {
    return $this -> getInfo() -> name;
  }

}
