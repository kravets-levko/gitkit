<?php

namespace Classes\Git;

class Commit {

  private $repository;
  private $hash;

  private $info = null;

  private function fetchInfo() {
    if ($this -> info === null) {
      $fields = [
        'author' => '%an',
        'authorEmail' => '%ae',
        'authorDate' => '%aI',
        'comitter' => '%cn',
        'comitterEmail' => '%ce',
        'comitterDate' => '%cI',
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

  public function getMessage() {
    $info = $this -> fetchInfo();
    return $info -> message;
  }

}
