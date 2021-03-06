<?php

namespace Actions\Repositories;

use Actions\Action;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Index extends Action {

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
      $a = @$a -> latestCommit -> info -> committerDate;
      $a = $a instanceof \DateTime ? $a -> format('U') : 0;

      $b = @$b -> latestCommit -> info -> committerDate;
      $b = $b instanceof \DateTime ? $b -> format('U') : 0;

      return $b - $a;
    });

    return $this -> render('repositories/index', [
      'repositories' => $repos,
      'group' => $group,
    ]);
  }

}
