<?php

namespace Git;

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

  protected function get_parents() {
    return $this -> cached(__METHOD__, function() {
      $hashes = $this -> context -> execute([
        'show', '--no-patch', '--format=%P', $this -> hash
      ]);
      return $this -> context -> commits(explode(' ', $hashes));
    });
  }

  protected function get_info() {
    return $this -> cached(__METHOD__, function() {
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
        explode("\n", $this -> context -> execute([
          'show', '--no-patch', '--format=' . $format, $this -> hash
        ]), count($fields)) // commit message may be multiline
      );
    });
  }

  protected function get_branches() {
    return $this -> cached(__METHOD__, function() {
      list($names, $defaultName) = $this -> context -> parseBranchList(
        $this -> context -> execute([
          'branch', '--contains', $this -> hash
        ])
      );

      $result = [];

      if ($defaultName !== null) {
        // Default branch first
        $branch = $this -> context -> branch($defaultName);
        if ($branch) {
          $result[$defaultName] = $branch;
        }
      }

      foreach ($names as $name) {
        $branch = $this -> context -> branch($name);
        if ($branch) {
          $result[$name] = $branch;
        }
      }

      return $result;
    });
  }

  protected function get_tags() {
    return $this -> cached(__METHOD__, function() {
      $names = $this -> context -> parseTagList(
        $this -> context -> execute(['tag', '--contains', $this -> hash])
      );

      $result = [];
      foreach ($names as $name) {
        $tag = $this -> context -> tag($name);
        if ($tag) {
          $result[$name] = $tag;
        }
      }

      return $result;
    });
  }

  protected function get_diff() {
    return $this -> cached(__METHOD__, function() {
      return new Diff($this -> context, $this);
    });
  }

}
