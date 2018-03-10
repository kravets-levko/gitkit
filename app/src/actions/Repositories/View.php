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
    $repos = $this -> repositories -> getRepositories();
    $groups = [];

    foreach ($repos as $repo) {
      list($group, $name) = explode('/', $repo -> name);
      @$groups[$group][$name] = $repo;
    }

    $group = isset($args['group']) ? $args['group'] : '';
    if ($group == '') $group = null;

    if ($group) {
      $repos = array_intersect_key($repos, [$group => true]);
    }

    return $this -> view -> render($response, 'pages/repositories/home.twig', [
      'repositories' => $groups,
      'group' => $group,
    ]);
  }

}
