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
      $lines = $this -> repository -> exec('branch', '--contains', $this -> hash);
      $lines = explode("\n", $lines);

      $this -> _branches = [];
      $defaultBranch = null;
      foreach ($lines as $line) {
        $line = explode(' ', trim($line));
        $isDefault = count($line) > 1;
        $line = array_last($line);
        $branch = $this -> repository -> branch($line);
        if ($branch) {
          $this -> _branches[$branch -> name] = $branch;
          if ($isDefault) $defaultBranch = $branch;
        }
      }

      if ($defaultBranch) {
        unset($this -> _branches[$defaultBranch -> name]);
      }

      ksort($this -> _branches);
      $this -> _branches = array_values($this -> _branches);

      if ($defaultBranch) array_unshift($this -> _branches, $defaultBranch);
    }
    return $this -> _branches;
  }

  protected function getTags() {
    if ($this -> _tags === null) {
      $lines = $this -> repository -> exec('tag', '--contains', $this -> hash);
      $lines = explode("\n", $lines);

      $this -> _tags = [];
      foreach ($lines as $line) {
        $tag = $this -> repository -> tag(trim($line));
        if ($tag) {
          $this -> _tags[$tag -> name] = $tag;
        }
      }

      ksort($this -> _tags);
      $this -> _tags = array_reverse(array_values($this -> _tags));
    }
    return $this -> _tags;
  }

}
