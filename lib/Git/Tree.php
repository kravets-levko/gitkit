<?php

namespace Git;

use Utils\Mixin\{ Properties, Cached };

class Tree {
  use Properties;
  use Cached;

  protected $context;
  protected $info;
  private $_ref;
  private $_parent;

  protected function get_parent() {
    return $this -> _parent;
  }

  protected function get_ref() {
    return $this -> _ref;
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

  protected function get_commit() {
    return $this -> cached(__METHOD__, function() {
      $hash = trim($this -> context -> execute([
        'rev-list', '-1', $this -> ref -> name, '--', $this -> path
      ]));
      return $this -> context -> commit($hash);
    });
  }

  protected function get_children() {
    return $this -> cached(__METHOD__, function() {
      $path = $this -> path;

      $lines = $this -> context -> execute([
        'ls-tree', '--long', '-z', $this -> ref -> name . ':' . $path
      ]);

      $lines = array_filter(explode("\0", $lines), 'strlen');

      $pathPrefix = $path == '' ? '' : $path . '/';

      $tree = [];
      $blob = [];
      foreach ($lines as $line) {
        list($description, $name) = explode("\t", $line, 2);
        list($mode, $type, $hash, $size) = array_values(array_filter(
          explode(' ', $description), 'strlen'));

        $info = (object)[
          'name' => $name,
          'path' => $pathPrefix . $name,
          'type' => strtolower($type),
          'mode' => $mode,
          'hash' => $hash,
          'size' => (int)$size,
        ];
        if ($info -> type == 'tree') {
          $tree[$info -> name] = new Tree($this -> context, $this -> ref, $this, $info);
        } elseif ($info -> type == 'blob') {
          $blob[$info -> name] = new Blob($this -> context, $this -> ref, $this, $info);
        }
      }

      ksort($tree);
      ksort($blob);
      return array_merge($tree, $blob);
    });
  }

  public function __construct(RepositoryContext $context, Ref $ref,
    Tree $parent = null, $info = null) {
    $this -> context = $context;
    $this -> _ref = $ref;
    $this -> _parent = $parent;
    $this -> info = is_object($info) ? $info : (object)[
      'name' => '',
      'path' => '',
      'type' => 'tree',
    ];
  }

  public function node(string $path, bool $detached = false) {
    if ($path == '') return $this;
    if ($detached) {
      $parentPath = pathinfo($path, PATHINFO_DIRNAME);
      $parentPath = $parentPath == '.' ? '' : $parentPath;
      $parent = new Tree($this -> context, $this -> ref, null, (object)[
        'name' => pathinfo($parentPath, PATHINFO_BASENAME),
        'path' => $parentPath,
        'type' => 'tree',
      ]);
      return $parent -> node(pathinfo($path, PATHINFO_BASENAME), false);
    } else {
      $path = explode('/', $path);
      $result = $this;
      while (count($path) > 0) {
        $fragment = array_shift($path);
        $children = $result -> children;
        if (array_key_exists($fragment, $children)) {
          $result = $children[$fragment];
        } else {
          return null;
        }
      }
      return $result;
    }
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
