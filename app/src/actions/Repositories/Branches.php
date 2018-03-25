<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Branches extends Action {

  public function post(Request $request, Response $response) {
    $params = $request -> getParsedBody();

    switch($params['action']) {
      case 'delete':
        $branch = $this -> repository -> branch($params['branch']);
        if ($branch) $branch -> delete();
        break;
    }

    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response, $args) {
    if (count($this -> repository -> branches) == 0) {
      $this -> notFound();
    }

    return $this -> render('repositories/branches', [
      'repository' => $this -> repository,
    ]);
  }

}
