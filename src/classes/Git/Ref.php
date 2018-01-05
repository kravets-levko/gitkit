<?php

namespace Classes\Git;

use Classes\Properties;

class Ref {
  use Properties;

  private $_repository;
  private $_name;

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

  protected function cached_commit() {
    $hash = trim($this -> repository -> git -> execute(['rev-list', '-1', $this -> name]));
    return $this -> repository -> commit($hash);
  }

  protected function cached_commits() {
    $hashes = $this -> repository -> git -> execute(['rev-list', $this -> name]);
    return $this -> repository -> commits(explode("\n", $hashes));
  }

  protected function get_head() {
    return $this -> commit;
  }

  protected function cached_tree() {
    return new Tree($this -> repository, $this);
  }

  public function __construct(Repository $repository, string $name) {
    $this -> _repository = $repository;
    $this -> _name = $name;
  }
}
