<?php

namespace Actions\SSH;

use \Slim\App;

class Router {

  public function __construct(App $app) {
    $app -> any('/ssh-keys', Keys::class);
  }

}
