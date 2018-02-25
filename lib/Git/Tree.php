<?php

namespace Git;

use Utils\Mixin\{ Properties, Cached };

class Tree {
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

  protected function get_children() {
    return $this -> cached(__METHOD__, function() {
      $path = $this -> path;

      $lines = $this -> context -> execute([
        'ls-tree', '--long', '-z', $this -> ref -> name . ':' . $path
      ]);
      $lines = array_filter(explode("\0", $lines), 'strlen');

      $result = [];
      foreach ($lines as $line) {
        list($description, $name) = explode("\t", $line, 2);
        list($mode, $type, $hash, $size) = array_values(array_filter(
          explode(' ', $description), 'strlen'));

        $info = (object)[
          'name' => $name,
          'path' => $path . '/' . $name,
          'type' => $type,
          'mode' => $mode,
          'hash' => $hash,
          'size' => (int)$size,
        ];

        if ($info -> type == 'tree') {
          $result[$info -> name] = new Tree($this -> context, $this, $info);
        } elseif ($info -> type == 'blob') {
          $result[$info -> name] = new Blob($this -> context, $this, $info);
        }
      }

      ksort($result);
      return $result;
    });
  }

  public function __construct(RepositoryContext $context, Tree $parent, mixed $info) {
    $this -> context = $context;
    $this -> _parent = $parent;
    $this -> info = $info;
  }

  public function children(...$globs) {
    $globs = prepare_string_list($globs, '*');
    if (count($globs) == 0) return [];

    return array_filter($this -> children, function($node) use ($globs) {
      foreach ($globs as $glob) {
        if (matches_glob($node -> path, $glob)) {
          return true;
        }
      }
      return false;
    });
  }

}
