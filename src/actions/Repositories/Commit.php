<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Commit
 *
 * @property \Slim\Views\Twig $view
 */
class Commit extends Action {

  public function get(Request $request, Response $response, $args) {
    $commit = isset($args['commit']) ? $this -> repository -> commit($args['commit']) : null;
    if (!$commit) {
      $this -> notFound();
    }

    return $this -> view -> render($response, 'pages/repositories/commit.twig', [
      'repository' => $this -> repository,
      'commit' => $commit,
    ]);
  }

}
