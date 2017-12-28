<?php

namespace Classes\Git\Core;

use \Classes\Git\Utils\Properties;

use \Classes\Git\Repository;

/**
 * Class Ref
 *
 * @property-read Repository $repository
 * @property-read string $name
 * @property-read string $type
 * @property-read Commit $commit
 */
class Ref {
  use Properties;

  private $_repository;
  private $_name;

  protected $_type = null;

  /**
   * @var Commit
   */
  private $_commit = null;

  public function __construct(Repository $repository, string $name) {
    $this -> _repository = $repository;
    $this -> _name = $name;
  }

  protected function getRepository() {
    return $this -> _repository;
  }

  protected function getName() {
    return $this -> _name;
  }

  protected function getType() {
    return $this -> _type;
  }

  protected function getCommit() {
    if ($this -> _commit === null) {
      $hash = trim($this -> repository -> exec(['rev-list', '-1', $this -> name]));
      $this -> _commit = $this -> repository -> commit($hash);
    }
    return $this -> _commit;
  }

}
