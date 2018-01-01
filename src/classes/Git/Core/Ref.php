<?php

namespace Classes\Git\Core;

use \Classes\Properties;
use \Classes\Git\Repository;

/**
 * Class Ref
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 * @property-read Commit[] $commits
 * @property-read Commit $head
 * @property-read Tree $tree
 */
class Ref {
  use Properties;

  private $_repository;
  private $_name;
  private $_tree = null;
  private $_commits = null;
  private $_commit = null;

  protected $_type = null;

  public function __construct(Repository $repository, string $name) {
    $this -> _repository = $repository;
    $this -> _name = $name;
  }

  protected function getRepository() {
    return $this -> _repository;
  }

  protected function getName() {
    return $this -> _name;
  }

  protected function getType() {
    return $this -> _type;
  }

  protected function getCommit() {
    if ($this -> _commit === null) {
      $hash = trim($this -> repository -> exec(['rev-list', '-1', $this -> name]));
      $this -> _commit = $this -> repository -> commit($hash);
    }
    return $this -> _commit;
  }

  protected function getCommits() {
    if ($this -> _commits === null) {
      $hashes = $this -> repository -> exec('rev-list', $this -> name);
      $this -> _commits = $this -> repository -> commits(explode("\n", $hashes));
    }
    return $this -> _commits;
  }

  protected function getHead() {
    return $this -> commit;
  }

  protected function getTree() {
    if ($this -> _tree === null) {
      $this -> _tree = new Tree($this -> repository, $this);
    }
    return $this -> _tree;
  }
}
