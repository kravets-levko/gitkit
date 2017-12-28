<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class Commit
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 *
 * @property-read string $hash
 * @property-read string $abbreviatedHash
 * @property-read Commit[] $parents
 * @property-read \stdClass $info
 * @property-read string $message
 * @property-read Tree $tree
 * @property-read Diff $diff
 */
class Commit extends Ref {

  protected $_type = 'commit';

  private $_info = null;
  private $_diff = null;
  private $_parents = null;
  private $_tree = null;

  protected function getCommit() {
    return $this;
  }

  protected function getHash() {
    return $this -> name;
  }

  protected function getAbbreviatedHash() {
    return substr($this -> hash, 0, 7);
  }

  protected function getParents() {
    if ($this -> _parents === null) {
      $hashes = $this -> repository -> exec(
        'show', '--no-patch', '--format=%P', $this -> hash
      );
      $this -> _parents = $this -> repository -> commits(explode(' ', $hashes));
    }
    return $this -> _parents;
  }

  protected function getInfo() {
    if ($this -> _info === null) {
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

      $this -> _info = (object)array_combine(
        array_keys($fields),
        explode("\n", $this -> repository -> exec(
          'show', '--no-patch', '--format=' . $format, $this -> hash
        ), count($fields)) // commit message may be multiline
      );
    }
    return $this -> _info;
  }

  protected function getMessage() {
    return $this -> info -> message;
  }

  protected function getDiff() {
    if ($this -> _diff === null) {
      $this -> _diff = new Diff($this -> repository, $this);
    }
    return $this -> _diff;
  }

  protected function getTree() {
    if ($this -> _tree === null) {
      $this -> _tree = new Tree($this -> repository, $this);
    }
    return $this -> _tree;
  }

}
