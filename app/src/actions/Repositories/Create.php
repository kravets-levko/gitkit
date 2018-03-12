<?php

namespace Actions\Repositories;

use Actions\Action;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class View
 *
 * @property \Slim\Views\Twig $view
 */
class Create extends Action {

  public function post(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $params = $request -> getParsedBody();
    $path = $config -> repositoriesRoot . '/' . $params['group'] . '/' .
      $params['name'] . '.git';
    $repository = $this -> repositories -> createRepository($path);
    return $response -> withRedirect('/' . $repository -> name, 302);
  }

  public function get(Request $request, Response $response, $args) {
    return $this -> view -> render($response, 'pages/repositories/create.twig', [
      'group' => $request -> getParam('group'),
    ]);
  }

}
