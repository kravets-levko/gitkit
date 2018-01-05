<?php

namespace Actions\Repositories;

use Slim\App;

class Router {

  public function __construct(App $app) {
    $app -> group('/{group}/{name}', function(App $group) {
      $group -> any('/commit/{commit}', Commit::class);
      $group -> any('/tree/{ref:[^:]*}[:{path:.*}]', Tree::class);
      $group -> any('/blob/{ref:[^:]*}[:{path:.*}]', Blob::class);
      $group -> any('/commits[/{ref:.*}]', Commits::class);
      $group -> any('/branches', Branches::class);
      $group -> any('/tags', Tags::class);
      $group -> any('', Tree::class);
    });

    $app -> any('/[{group}]', View::class);
  }

}
