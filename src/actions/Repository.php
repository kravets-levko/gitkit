<?php

namespace Actions;

use Classes\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Repository extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);
    return $this -> view -> render($response, 'pages/repository.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
    ]);
  }

}
