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

 * @property string $data
 */
class TreeFile extends Blob {

  protected function getData() {
    return $this -> repository -> exec(
      'show',
      $this -> commit -> hash . ':' . $this -> path
    );
  }

}
