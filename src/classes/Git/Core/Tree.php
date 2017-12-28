<?php

namespace Classes\Git\Core;

use \Classes\Git\Utils\Properties;

use \Classes\Git\Repository;

/**
 * Class Tree
 *
 * @property-read Repository $repository
 * @property-read Ref $ref
 *
 * @property-read string[] $filenames
 * @property-read TreeFolder $root
 */
class Tree {
  use Properties;

  private $_repository;
  private $_ref;

  /**
   * @var TreeFolder
   */
  private $_root = null;
  private $_filenames = null;
  private $filesByPath = [];
  private $infoByPath = [];

  public function __construct(Repository $repository, Ref $ref) {
    $this -> _repository = $repository;
    $this -> _ref = $ref;
  }

  protected function getRoot() {
    if ($this -> _root === null) {
      $this -> _root = new TreeFolder($this -> repository, $this, '');
    }
    return $this -> _root;
  }

  protected function getRepository() {
    return $this -> _repository;
  }

  protected function getRef() {
    return $this -> _ref;
  }

  public function filenames(...$globs) {
    if (count($globs) == 0) {
      $globs = ['*'];
    } elseif ((count($globs) == 1) && is_array($globs[0])) {
      $globs = $globs[0];
    }
    $globs = array_filter($globs, 'is_string');
    $globs = array_filter($globs, 'strlen'); // empty glob matches nothing
    if (count($globs) == 0) {
      return [];
    }

    if ($this -> _filenames === null) {
      $lines = $this -> repository -> exec('ls-tree', '--name-only', '-r', '-z', $this -> ref -> name);
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

      $lines = $this -> repository -> exec(
        'ls-tree', '--long', '-z', $this -> ref -> name . ':' . $path
      );
      $lines = array_filter(explode("\0", $lines), 'strlen');

      foreach ($lines as $line) {
        list($description, $name) = explode("\t", $line, 2);
        list($mode, $type, $hash, $size) = array_values(array_filter(
          explode(' ', $description), 'strlen'));
        $fullPath = $pathPrefix . $name;

        $this -> infoByPath[$fullPath] = (object)[
          'name' => $name,
          'path' => $fullPath,
          'type' => $type,
          'mode' => $mode,
          'hash' => $hash,
          'size' => (int)$size,
        ];
      }
    }

    $path = $path == '' ? '/' : '/' . $path . '/';
    return array_values(array_filter($this -> infoByPath, function($key) use ($path) {
      return strpos('/' . $key, $path) === 0;
    }, ARRAY_FILTER_USE_KEY));
  }

  public function nodeInfo($path) {
    $path = trim($path, '/');
    if ($path == '') {
      return (object)[
        'name' => '',
        'path' => '',
        'type' => 'tree',
        'mode' => '000000',
        'hash' => str_repeat('0', 40),
        'size' => 0,
      ];
    }
    if (!array_key_exists($path, $this -> infoByPath)) {
      $parentPath = explode('/', $path);
      array_pop($parentPath);
      $parentPath = implode('/', $parentPath);
      // Cache children info for parent of $path ($path will be among them)
      $this -> childrenInfo($parentPath);
    }
    return @$this -> infoByPath[$path];
  }

}
