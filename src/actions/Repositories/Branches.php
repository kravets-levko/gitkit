<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Branches
 *
 * @property \Slim\Views\Twig $view
 */
class Branches extends Action {

  public function get(Request $request, Response $response, $args) {
    return $this -> view -> render($response, 'pages/repositories/branches.twig', [
      'repository' => $this -> repository,
    ]);
  }

}
