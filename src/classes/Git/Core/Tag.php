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
 *
 * @property-read Commit $head
 * @property-read Commit[] $commits
 * @property-read Tree $tree
 */
class Tag extends Branch {

  protected $_type = 'tag';

}
