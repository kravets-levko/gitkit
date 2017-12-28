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

  public function getRepository() {
    return $this -> _repository;
  }

  public function getRef() {
    return $this -> _ref;
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
      // Cache children info for parent of $path ($path will be among them)
      $this -> childrenInfo(pathinfo($path, PATHINFO_DIRNAME));
    }
    return $this -> infoByPath[$path];
  }

}
