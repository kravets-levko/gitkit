<?php

namespace Classes\Git;

use Classes\Properties;

class Ref {
  use Properties;

  private $_repository;
  private $_name;
  private $_tree = null;
  private $_commits = null;
  private $_commit = null;

  protected $_type = null;

  protected function get_repository() {
    return $this -> _repository;
  }

  protected function get_name() {
    return $this -> _name;
  }

  protected function get_type() {
    return $this -> _type;
  }

  protected function get_commit() {
    if ($this -> _commit === null) {
      $hash = trim($this -> repository -> git -> execute(['rev-list', '-1', $this -> name]));
      $this -> _commit = $this -> repository -> commit($hash);
    }
    return $this -> _commit;
  }

  protected function get_commits() {
    if ($this -> _commits === null) {
      $hashes = $this -> repository -> git -> execute(['rev-list', $this -> name]);
      $this -> _commits = $this -> repository -> commits(explode("\n", $hashes));
    }
    return $this -> _commits;
  }

  protected function get_head() {
    return $this -> commit;
  }

  protected function get_tree() {
    if ($this -> _tree === null) {
      $this -> _tree = new Tree($this -> repository, $this);
    }
    return $this -> _tree;
  }

  public function __construct(Repository $repository, string $name) {
    $this -> _repository = $repository;
    $this -> _name = $name;
  }
}
