<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

class Ref {

  private $repository;
  private $ref;

  /**
   * @var Commit
   */
  private $commit = null;

  public function __construct(Repository $repository, string $ref) {
    $this -> repository = $repository;
    $this -> ref = $ref;
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getRef() {
    return $this -> ref;
  }
  public function getRefType() {
    return null;
  }


  public function getCommit() {
    if ($this -> commit === null) {
      $hash = trim($this -> getRepository() -> exec(['rev-list', '-1', $this -> ref]));
      $this -> commit = $this -> getRepository() -> getCommit($hash);
    }
    return $this -> commit;
  }

}
