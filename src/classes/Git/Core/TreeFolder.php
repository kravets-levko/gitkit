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

}
