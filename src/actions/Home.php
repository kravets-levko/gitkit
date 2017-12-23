<?php

namespace Actions;

use Classes\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Home extends Action {

  public function get(Request $request, Response $response) {
    $config = $this -> container -> get('config');
    $repos = \Classes\Git\Repository::getRepositories($config);
    return $this -> view -> render($response, 'pages/repository.twig', [
      'repositories' => $repos,
    ]);
  }

}
