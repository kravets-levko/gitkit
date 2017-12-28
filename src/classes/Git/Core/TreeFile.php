<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class TreeFile
 *
 * @property-read Repository $repository
 * @property-read Tree $tree
 * @property-read string $path
 * @property-read \stdClass $info
 * @property-read Commit $commit
 * @property-read string $type
 * @property-read string $name

 * @property-read string $data
 * @property-read string $ext
 * @property-read string $mime
 */
class TreeFile extends Blob {

  protected function getData() {
    return $this -> repository -> exec(
      'show',
      $this -> commit -> hash . ':' . $this -> path
    );
  }

  protected function getExt() {
    $result = pathinfo($this -> path, PATHINFO_EXTENSION);
    return is_string($result) ? $result : '';
  }

  protected function getMime() {
    return media_type_from_filename($this -> path);
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
    $this -> repository -> passthru(
      'show',
      $this -> commit -> hash . ':' . $this -> path
    );
  }

}
