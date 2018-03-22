<?php

namespace Actions\Settings;

use Slim\App;

class Router {

  public function __construct(App $app) {
    $app -> group('/settings', function(App $group) {
      $group -> any('/keys', Keys::class);
      $group -> any('/system', System::class);
    });
  }

}
