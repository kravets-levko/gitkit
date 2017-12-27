<?php

namespace Classes\Git\Core;

class TreeFile extends Blob {

  public function getData() {
    return $this -> getRepository() -> exec(
      'show',
      $this -> getCommit() -> getHash() . ':' . $this -> getPath()
    );
  }

}
