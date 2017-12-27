<?php

namespace Classes\Git\Core;

class Commit extends Ref {

  private $info = null;
  private $diff = null;
  private $parents = null;
  private $tree = null;

  public function getRefType() {
    return 'commit';
  }

  public function getCommit() {
    return $this;
  }

  public function getHash() {
    return $this -> getRef();
  }

  public function getAbbreviatedHash() {
    return substr($this -> getHash(), 0, 7);
  }

  public function getParents() {
    if ($this -> parents === null) {
      $hashes = $this -> getRepository() -> exec([
        'show', '--no-patch', '--format=%P', $this -> getHash()
      ]);
      $this -> parents = $this -> getRepository() -> getCommits(explode(' ', $hashes));
    }
    return $this -> parents;
  }

  public function getInfo() {
    if ($this -> info === null) {
      $fields = [
        'author' => '%an',
        'authorEmail' => '%ae',
        'authorDate' => '%at',
        'committer' => '%cn',
        'committerEmail' => '%ce',
        'committerDate' => '%ct',
        'message' => '%B',
      ];

      $format = implode("%n", array_values($fields));

      $this -> info = (object)array_combine(
        array_keys($fields),
        explode("\n", $this -> getRepository() -> exec(
          'show', '--no-patch', '--format=' . $format, $this -> getHash()
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
      $this -> diff = new Diff($this -> getRepository(), $this);
    }
    return $this -> diff;
  }

  public function getTree() {
    if ($this -> tree === null) {
      $this -> tree = new Tree($this -> getRepository(), $this);
    }
    return $this -> tree;
  }

}
