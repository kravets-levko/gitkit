<?php

namespace Classes\Git;

use Classes\Git\Utils\Parse;

class Commit extends Ref {

  protected $_type = 'commit';

  protected function get_commit() {
    return $this;
  }

  protected function get_hash() {
    return $this -> name;
  }

  protected function get_abbreviatedHash() {
    return substr($this -> hash, 0, 7);
  }

  protected function cached_parents() {
    $hashes = $this -> repository -> git -> execute([
      'show', '--no-patch', '--format=%P', $this -> hash
    ]);
    return $this -> repository -> commits(explode(' ', $hashes));
  }

  protected function cached_info() {
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

    return (object)array_combine(
      array_keys($fields),
      explode("\n", $this -> repository -> git -> execute([
        'show', '--no-patch', '--format=' . $format, $this -> hash
      ]), count($fields)) // commit message may be multiline
    );
  }

  protected function cached_diff() {
    return new Diff($this -> repository, $this);
  }

  protected function cached_branches() {
    list($names, $defaultName) = Parse::parseBranchList(
      $this -> repository -> git -> execute([
        'branch', '--contains', $this -> hash
      ])
    );

    $result = [];
    foreach ($names as $name) {
      $branch = $this -> repository -> branch($name);
      if ($branch) {
        $result[$branch -> name] = $branch;
      }
    }

    if ($defaultName !== null) {
      $defaultBranch = $result[$defaultName];
      unset($result[$defaultName]);
      array_unshift($result, $defaultBranch);
    }

    return array_values($result);
  }

  protected function cached_tags() {
    $names = Parse::parseTagList(
      $this -> repository -> git -> execute(['tag', '--contains', $this -> hash])
    );

    $result = [];
    foreach ($names as $name) {
      $tag = $this -> repository -> tag($name);
      if ($tag) {
        $result[] = $tag;
      }
    }

    return $result;
  }

}
