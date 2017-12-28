<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class TreeFolder
 *
 * @property-read Repository $repository
 * @property-read Tree $tree
 * @property-read string $path
 * @property-read \stdClass $info
 * @property-read Commit $commit
 * @property-read string $type
 * @property-read string $name

 * @property (Blob | TreeFolder | TreeFile)[] $nodes
 */
class TreeFolder extends Blob {

  public function children() {
    return $this -> tree -> nodes($this -> path);
  }

  public function filenames(...$globs) {
    $globs = prepare_string_list($globs, '*');
    if (count($globs) == 0) return [];

    // Add prefix to each glob
    $globs = array_map(function($glob) {
      return $this -> path . '/' . $glob;
    }, $globs);

    return $this -> tree -> filenames($globs);
  }

}
