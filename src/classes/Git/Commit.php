<?php

namespace Classes\Git;

class Commit {

  private $repository;
  private $hash;

  private $info = null;
  private $diff = null;
  private $parents = null;

  public function __construct(Repository $repository, string $hash) {
    $this -> repository = $repository;
    $this -> hash = $hash;
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getHash() {
    return $this -> hash;
  }

  public function getParents() {
    if ($this -> parents === null) {
      $result = $this -> repository -> exec(['show', '--no-patch', '--format=%P']);
      $hashes = array_filter(array_map('trim', explode(' ', $result)), 'strlen');
      $this -> parents = array_map(function($hash) {
        return $this -> repository -> getCommit($hash);
      }, $hashes);
    }
    return $this -> parents;
  }

  public function getInfo() {
    if ($this -> info === null) {
      $fields = [
        'author' => '%an',
        'authorEmail' => '%ae',
        'authorDate' => '%aI',
        'committer' => '%cn',
        'committerEmail' => '%ce',
        'committerDate' => '%cI',
        'message' => '%B',
      ];

      $format = implode("%n", array_values($fields));

      $this -> info = (object)array_combine(
        array_keys($fields),
        explode("\n", $this -> repository -> exec(
          'show', '--no-patch', '--format=' . $format, $this -> hash
        ), count($fields)) // commit message may be multiline
      );
    }
    return $this -> info;
  }

  public function getMessage() {
    $info = $this -> getInfo();
    return $info -> message;
  }

  public function getAuthor() {
    $info = $this -> getInfo();
    $result = [];
    if ($info -> author) $result[] = $info -> author;
    if ($info -> authorEmail) $result[] = '<' . $info -> authorEmail . '>';
    return implode(' ', $result);
  }

  public function getCommitter() {
    $info = $this -> getInfo();
    $result = [];
    if ($info -> committer) $result[] = $info -> committer;
    if ($info -> committerEmail) $result[] = '<' . $info -> committerEmail . '>';
    return implode(' ', $result);
  }

  public function getDiff() {
    if ($this -> diff === null) {
      $this -> diff = new Diff($this -> repository, $this);
    }
    return $this -> diff;
  }

}
