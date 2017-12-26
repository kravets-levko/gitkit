<?php

namespace Classes\Git;

class Branch {

  private $repository;
  private $name;

  /**
   * @var Commit
   */
  private $latestCommit = null;
  /**
   * @var Commit[]
   */
  private $commits = null;

  public function __construct(Repository $repository, string $name) {
    $this -> repository = $repository;
    $this -> name = $name;
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getName() {
    return $this -> name;
  }

  public function getLatestCommit() {
    if ($this -> latestCommit === null) {
      $hash = trim($this -> repository -> exec(['rev-list', '-1', $this -> name]));
      $this -> latestCommit = $this -> repository -> getCommit($hash);
    }
    return $this -> latestCommit;
  }

  public function getCommits() {
    if ($this -> commits === null) {
      $hashes = $this -> repository -> exec(['rev-list', $this -> name]);
      $this -> commits = $this -> repository -> getCommits(explode("\n", $hashes));
    }
    return $this -> commits;
  }

  public function getFiles($path = '') {
    $commit = $this -> getLatestCommit();
    return $commit ? $commit -> getFiles($path) : [];
  }

}
