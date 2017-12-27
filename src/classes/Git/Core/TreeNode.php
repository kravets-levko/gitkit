<?php

namespace Classes\Git\Core;

class TreeNode extends Blob {

  public function getNodes() {
    return $this -> getTree() -> getNodes($this -> getPath());
  }

}
