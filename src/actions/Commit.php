<?php

namespace Actions;

use Classes\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Repository
 *
 * @property \Slim\Views\Twig $view
 */
class Commit extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);

    $commit = isset($args['commit']) ? $repo -> commit($args['commit'], true) : null;
    if (!$commit) {
      $this -> notFound();
    }

    return $this -> view -> render($response, 'pages/commit.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'commit' => $commit,
    ]);
  }

}
