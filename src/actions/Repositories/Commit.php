<?php

namespace Actions\Repositories;

use Actions\Action;
use Classes\Git\Repository as GitRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Commit
 *
 * @property \Slim\Views\Twig $view
 */
class Commit extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = GitRepository::getRepository($args['group'] . '/' . $args['name'], $config);

    $commit = isset($args['commit']) ? $repo -> commit($args['commit'], true) : null;
    if (!$commit) {
      $this -> notFound();
    }

    return $this -> view -> render($response, 'pages/commit.twig', [
      'repository' => $repo,
      'commit' => $commit,
    ]);
  }

}
