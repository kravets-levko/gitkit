<?php

namespace Git;

use Process\Process;
use Utils\Mixin\{ Properties, Cached };

class Blob {
  use Properties;
  use Cached;

  protected $context;
  protected $info;
  private $_parent;

  protected function get_parent() {
    return $this -> _parent;
  }

  protected function get_ref() {
    return $this -> _parent ? $this -> _parent -> ref : null;
  }

  protected function get_name() {
    return $this -> info -> name;
  }

  protected function get_path() {
    return $this -> info -> path;
  }

  protected function get_type() {
    return $this -> info -> type;
  }

  protected function get_mode() {
    return $this -> info -> mode;
  }

  protected function get_ext() {
    $result = pathinfo($this -> info -> path, PATHINFO_EXTENSION);
    return is_string($result) ? $result : '';
  }

  protected function get_data() {
    return $this -> context -> execute([
      'show', $this -> ref -> hash . ':' . $this -> path
    ]);
  }

  protected function get_mime() {
    return $this -> cached(__METHOD__, function() {
      $process = $this -> context -> git -> start([
        'show', $this -> ref -> hash . ':' . $this -> path
      ]);
      $process -> stdin() -> close();
      $process -> stderr() -> close();

      $process = new Process('file --brief --mime-type -', null, null, [
        0 => $process -> stdout(),
      ]);
      $result = strtolower(trim($process -> stdout() -> read()));
      $process -> close();

      if ($result == 'text/plain') {
        if (in_array(strtolower($this -> ext), ['md', 'markdown'])) {
          $result = 'text/markdown';
        }
      }

      return $result;
    });
  }

  protected function get_lines() {
    return $this -> cached(__METHOD__, function() {
      $result = (object)[
        'total' => 0,
        'nonEmpty' => 0,
      ];

      // Total lines
      $process = $this -> context -> git -> start([
        'show', $this -> ref -> hash . ':' . $this -> path
      ]);
      $process -> stdin() -> close();
      $process -> stderr() -> close();

      $process = new Process('wc --lines -', null, null, [
        0 => $process -> stdout(),
      ]);
      $result -> total = (int)(trim($process -> stdout() -> read()));
      $process -> close();

      // Non-empty lines
      $process = $this -> context -> git -> start([
        'show', $this -> commit -> hash . ':' . $this -> path
      ]);
      $process -> stdin() -> close();
      $process -> stderr() -> close();

      $process = new Process('grep --count --invert-match --line-regexp \'^\s*$\'', null, null, [
        0 => $process -> stdout(),
      ]);
      $result -> nonEmpty = (int)(trim($process -> stdout() -> read()));
      $process -> close();

      return $result;
    });
  }

  public function __construct(RepositoryContext $context, Tree $parent, mixed $info) {
    $this -> context = $context;
    $this -> _parent = $parent;
    $this -> info = $info;
  }

  public function matchesMime(...$mimeTypes) {
    $mimeTypes = prepare_string_list($mimeTypes, []);
    foreach ($mimeTypes as $pattern) {
      if (matches_mime($this -> mime, $pattern)) {
        return true;
      }
    }
    return false;
  }

  public function displayData() {
    return $this -> context -> execute([
      'show',
      $this -> commit -> hash . ':' . $this -> path
    ], true);
  }

}
