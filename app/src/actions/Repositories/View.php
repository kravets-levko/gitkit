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

    $group = isset($args['group']) ? $args['group'] : '';
    if ($group != '') {
      $repos = array_filter($repos, function($repo) use ($group) {
        list($repositoryGroup) = explode('/', $repo -> name);
        return $repositoryGroup == $group;
      });
    }

    usort($repos, function($a, $b) {
      $a = $a -> latestCommit -> info -> committerDate -> format('U');
      $b = $b -> latestCommit -> info -> committerDate -> format('U');
      return $b - $a;
    });

    return $this -> view -> render($response, 'pages/repositories/home.twig', [
      'repositories' => $repos,
      'group' => $group,
    ]);
  }

}
