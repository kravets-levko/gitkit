<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class Branch
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 * @property-read Commit[] $commits
 * @property-read Commit $head
 * @property-read Tree $tree
 */
class Branch extends Ref {

  protected $_type = 'branch';

}
