<?php

namespace Models\Repositories;

use Classes\Model;

class Create extends Model {

  protected function getValidators($v) {
    return [
      'group' => $v::stringType() -> length(1, 250) -> alnum('-_') -> noWhitespace(),
      'name' => $v::stringType() -> length(1, 250) -> alnum('-_') -> noWhitespace(),
      'description' => $v::alwaysValid(),
    ];
  }

}
