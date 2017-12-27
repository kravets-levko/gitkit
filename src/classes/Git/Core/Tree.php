<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

class Tree {

  private $repository;
  private $ref;

  private $filesByPath = [];
  private $infoByPath = [];

  public function __construct(Repository $repository, Ref $ref) {
    $this -> repository = $repository;
    $this -> ref = $ref;
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getRef() {
    return $this -> ref;
  }

  public function getNodes($path = '') {
    $path = trim($path, '/');
    if (!array_key_exists($path, $this -> filesByPath)) {
      $items = $this -> getChildrenInfo($path);

      $nodes = [];
      $files = [];

      foreach ($items as $info) {
        if ($info -> type == 'tree') {
          $nodes[$info -> name] = new TreeNode($this -> getRepository(), $this, $info -> path);
        } elseif ($info -> type == 'blob') {
          $files[$info -> name] = new TreeFile($this -> getRepository(), $this, $info -> path);
        }
      }

      ksort($nodes);
      ksort($files);

      $this -> filesByPath[$path] = array_merge(array_values($nodes), array_values($files));
    }

    return $this -> filesByPath[$path];
  }

  public function getChildrenInfo($path) {
    $path = trim($path, '/');
    if (!array_key_exists($path, $this -> infoByPath)) {
      $pathPrefix = $path == '' ? '' : $path . '/';

      $lines = $this -> getRepository() -> exec(
        'ls-tree', '--long', '-z', $this -> getRef() -> getRef() . ':' . $path
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

  public function getNodeInfo($path) {
    $path = trim($path, '/');
    if (!array_key_exists($path, $this -> infoByPath)) {
      // Cache children info for parent of $path ($path will be among them)
      $this -> getChildrenInfo(pathinfo($path, PATHINFO_DIRNAME));
    }
    return $this -> infoByPath[$path];
  }

}
