<?php

namespace Classes\Git;

use Classes\Properties;

class Tree {
  use Properties;

  private $_repository;
  private $_ref;

  /**
   * @var TreeFolder
   */
  private $_rootInfo = null;
  private $_filenames = null;
  private $filesByPath = [];
  private $infoByPath = [];

  protected function cached_root() {
    return new TreeFolder($this -> repository, $this, '');
  }

  protected function get_repository() {
    return $this -> _repository;
  }

  protected function get_ref() {
    return $this -> _ref;
  }

  public function __construct(Repository $repository, Ref $ref) {
    $this -> _repository = $repository;
    $this -> _ref = $ref;

    $this -> _rootInfo = (object)[
      'name' => '',
      'path' => '',
      'type' => 'tree',
      'mode' => '000000',
      'hash' => str_repeat('0', 40),
      'size' => 0,
    ];
  }

  public function filenames(...$globs) {
    $globs = prepare_string_list($globs, '*');
    if (count($globs) == 0) return [];

    if ($this -> _filenames === null) {
      $lines = $this -> repository -> git -> execute([
        'ls-tree', '--name-only', '-r', '-z', $this -> ref -> name
      ]);
      $this -> _filenames = explode("\0", $lines);
    }
    return array_values(array_filter($this -> _filenames, function($name) use ($globs) {
      foreach ($globs as $glob) {
        if (matches_glob($name, $glob)) {
          return true;
        }
      }
      return false;
    }));
  }

  public function nodes($path = '') {
    $path = trim($path, '/');
    if (!array_key_exists($path, $this -> filesByPath)) {
      $items = $this -> childrenInfo($path);

      $nodes = [];
      $files = [];

      foreach ($items as $info) {
        if ($info -> type == 'tree') {
          $nodes[$info -> name] = new TreeFolder($this -> repository, $this, $info -> path);
        } elseif ($info -> type == 'blob') {
          $files[$info -> name] = new TreeFile($this -> repository, $this, $info -> path);
        }
      }

      ksort($nodes);
      ksort($files);

      $this -> filesByPath[$path] = array_merge(array_values($nodes), array_values($files));
    }

    return $this -> filesByPath[$path];
  }

  public function node($path) {
    $path = trim($path, '/');
    if ($path == '') return $this -> root;

    $parentPath = explode('/', $path);
    $fileName = array_pop($parentPath);
    $parentPath = implode('/', $parentPath);

    $children = $this -> nodes($parentPath);
    foreach ($children as $item) {
      if ($item -> name == $fileName) {
        return $item;
      }
    }
    return null;
  }

  public function childrenInfo($path) {
    $path = trim($path, '/');
    if (!array_key_exists($path, $this -> infoByPath)) {
      $pathPrefix = $path == '' ? '' : $path . '/';

      $lines = $this -> repository -> git -> execute([
        'ls-tree', '--long', '-z', $this -> ref -> name . ':' . $path
      ]);
      $lines = array_filter(explode("\0", $lines), 'strlen');

      $children = [];
      foreach ($lines as $line) {
        list($description, $name) = explode("\t", $line, 2);
        list($mode, $type, $hash, $size) = array_values(array_filter(
          explode(' ', $description), 'strlen'));
        $fullPath = $pathPrefix . $name;

        $children[$name] = (object)[
          'name' => $name,
          'path' => $fullPath,
          'type' => $type,
          'mode' => $mode,
          'hash' => $hash,
          'size' => (int)$size,
        ];
      }

      ksort($children);
      $this -> infoByPath[$path] = array_values($children);
    }

    return $this -> infoByPath[$path];
  }

  public function nodeInfo($path) {
    $path = trim($path, '/');
    if ($path == '') return $this -> _rootInfo;

    $parentPath = explode('/', $path);
    $fileName = array_pop($parentPath);
    $parentPath = implode('/', $parentPath);

    $children = $this -> childrenInfo($parentPath);
    foreach ($children as $item) {
      if ($item -> name == $fileName) {
        return $item;
      }
    }
    return null;
  }
}
