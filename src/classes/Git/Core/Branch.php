<?php

namespace Classes\Git\Core;

class Branch extends Ref {

  private $commits = null;

  public function getRefType() {
    return 'branch';
  }

  public function getName() {
    return $this -> getRef();
  }

  public function getHead() {
    return $this -> getCommit();
  }

  public function getCommits() {
    if ($this -> commits === null) {
      $hashes = $this -> getRepository() -> exec(['rev-list', $this -> getName()]);
      $this -> commits = $this -> getRepository() -> getCommits(explode("\n", $hashes));
    }
    return $this -> commits;
  }

  public function getTree() {
    return $this -> getHead() -> getTree();
  }

}
