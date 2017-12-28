<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

/**
 * Class Tag
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 * @property-read Commit[] $commits
 * @property-read Commit $head
 * @property-read Tree $tree
 */
class Tag extends Ref {

  protected $_type = 'tag';

}
