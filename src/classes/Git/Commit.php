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

  public function getAbbrHash() {
    return substr($this -> hash, 0, 7);
  }

  public function getParents() {
    if ($this -> parents === null) {
      $hashes = $this -> repository -> exec(['show', '--no-patch', '--format=%P', $this -> hash]);
      $this -> parents = $this -> repository -> getCommits(explode(' ', $hashes));
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

  public function getFiles($path = '') {
    $result = $this -> repository -> exec('ls-tree', $this -> hash . ':' . trim($path));

    $result = array_filter(array_map(function($line) {
      $line = trim($line);
      if ($line == '') return null;
      list($description, $path) = explode("\t", $line, 2);
      list($mode, $type) = explode(' ', $description);
      $commit = $this -> repository -> getCommit(trim($this -> repository -> exec(
        'rev-list', '-1', $this -> hash, '--', $path
      )));
      return (object)[
        'path' => $path,
        'commit' => $commit,
        'type' => $type,
        'mode' => $mode,
      ];
    }, explode("\n", $result)));

    $typeMap = [
      'tree' => 0,
      'blob' => 1,
    ];

    usort($result, function($a, $b) use ($typeMap) {
      if ($a -> type == $b -> type) {
        return strcmp($a -> path, $b -> path);
      }
      return $typeMap[$a -> type] - $typeMap[$b -> type];
    });

    return $result;
  }

}
