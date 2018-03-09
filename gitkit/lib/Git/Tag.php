<?php

namespace Git;

class Tag extends Ref {

  protected $_type = 'tag';

  public function delete() {
    $this -> context -> execute(['tag', '--delete', $this -> name]);
  }

}
