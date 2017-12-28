<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class Branch
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 *
 * @property-read Commit $head
 * @property-read Commit[] $commits
 * @property-read Tree $tree
 */
class Branch extends Ref {

  protected $_type = 'branch';

  private $_commits = null;

  protected function getHead() {
    return $this -> commit;
  }

  protected function getCommits() {
    if ($this -> _commits === null) {
      $hashes = $this -> repository -> exec(['rev-list', $this -> name]);
      $this -> _commits = $this -> repository -> commits(explode("\n", $hashes));
    }
    return $this -> _commits;
  }

  protected function getTree() {
    return $this -> head -> tree;
  }

}
