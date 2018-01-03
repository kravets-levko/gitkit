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
class View extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repos = \Classes\Git\Repository::getRepositories($config);

    $group = isset($args['group']) ? $args['group'] : '';
    if ($group == '') $group = null;

    if ($group) {
      $repos = array_intersect_key($repos, [$group => true]);
    }

    return $this -> view -> render($response, 'pages/home.twig', [
      'repositories' => $repos,
      'group' => $group,
    ]);
  }

}
