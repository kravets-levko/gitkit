<?php

namespace Actions\Repositories;

use \Actions\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Commits
 *
 * @property \Slim\Views\Twig $view
 */
class Commits extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);

    if (isset($args['ref'])) {
      $ref = $repo -> ref($args['ref']);
    } else {
      $ref = $repo -> defaultBranch;
    }
    if (!$ref) {
      $this -> notFound();
    }

    // TODO: Pagination
    // TODO: Group by date

    return $this -> view -> render($response, 'pages/commits.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'ref' => $ref,
    ]);
  }

}
