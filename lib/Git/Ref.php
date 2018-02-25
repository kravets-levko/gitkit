<?php

namespace Git;

use Utils\Mixin\{ Properties, Cached };

class Ref {
  use Properties;
  use Cached;

  protected $context;
  private $_name;

  protected $_type = null;

  protected function get_name() {
    return $this -> _name;
  }

  protected function get_type() {
    return $this -> _type;
  }

  protected function get_commit() {
    return $this -> cached(__METHOD__, function() {
      $hash = trim($this -> context -> execute(['rev-list', '-1', $this -> name, '--']));
      return $this -> repository -> commit($hash);
    });
  }

  protected function get_commits() {
    return $this -> cached(__METHOD__, function() {
      $hashes = $this -> context -> execute(['rev-list', $this -> name, '--']);
      return $this -> context -> commits(explode("\n", $hashes));
    });
  }

  protected function get_head() {
    return $this -> commit;
  }

  public function __construct(RepositoryContext $context, string $name) {
    $this -> context = $context;
    $this -> _name = $name;
  }
}
