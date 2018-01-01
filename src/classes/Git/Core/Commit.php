<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;
use \Classes\Git\Utils\Parse;

/**
 * Class Commit
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 * @property-read Commit[] $commits
 * @property-read Commit $head
 * @property-read Tree $tree
 *
 * @property-read string $hash
 * @property-read string $abbreviatedHash
 * @property-read Commit[] $parents
 * @property-read \stdClass $info
 * @property-read Diff $diff
 * @property-read Branch[] $branches
 * @property-read Tag[] $tags
 */
class Commit extends Ref {

  protected $_type = 'commit';

  private $_info = null;
  private $_diff = null;
  private $_parents = null;
  private $_branches = null;
  private $_tags = null;

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

  protected function getDiff() {
    if ($this -> _diff === null) {
      $this -> _diff = new Diff($this -> repository, $this);
    }
    return $this -> _diff;
  }

  protected function getBranches() {
    if ($this -> _branches === null) {
      list($names, $defaultName) = Parse::parseBranchList(
        $this -> repository -> exec('branch', '--contains', $this -> hash)
      );

      $this -> _branches = [];
      foreach ($names as $name) {
        $branch = $this -> repository -> branch($name);
        if ($branch) {
          $this -> _branches[$branch -> name] = $branch;
        }
      }

      ksort($this -> _branches);
      if ($defaultName !== null) {
        $defaultBranch = $this -> _branches[$defaultName];
        unset($this -> _branches[$defaultName]);
        array_unshift($this -> _branches, $defaultBranch);
      }

      $this -> _branches = array_values($this -> _branches);
    }
    return $this -> _branches;
  }

  protected function getTags() {
    if ($this -> _tags === null) {
      $names = Parse::parseTagList(
        $this -> repository -> exec('tag', '--contains', $this -> hash)
      );
      $this -> _tags = [];
      foreach ($names as $name) {
        $tag = $this -> repository -> tag($name);
        if ($tag) {
          $this -> _tags[] = $tag;
        }
      }
    }
    return $this -> _tags;
  }

}
