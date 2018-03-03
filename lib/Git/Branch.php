<?php

namespace Git;

class Branch extends Ref {

  protected $_type = 'branch';

  public function delete() {
    $this -> context -> execute(['branch', '--delete', '--force', $this -> name]);
  }

}
