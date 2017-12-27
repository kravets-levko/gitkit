<?php

namespace Classes\Git\Core;

class Tag extends Branch {

  public function getRefType() {
    return 'tag';
  }

}
