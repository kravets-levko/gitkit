<?php

namespace Actions;

use Classes\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Commit extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);

    $commit = isset($args['commit']) ? $repo -> getCommit($args['commit']) : null;
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
