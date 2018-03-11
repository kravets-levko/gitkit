<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Tree
 *
 * @property \Slim\Views\Twig $view
 */
class Tree extends Action {

  public function get(Request $request, Response $response, $args) {
    if (isset($args['ref'])) {
      $ref = $this -> repository -> ref($args['ref']);
    } else {
      $ref = $this -> repository -> defaultBranch;
    }
    if (!$ref) {
      $this -> notFound();
    }
    $path = isset($args['path']) && is_string($args['path']) ? $args['path'] : '';

    /**
     * @var \Git\Tree $tree
     */
    $tree = $ref -> tree -> node($path, true);
    if (!$tree || ($tree -> type !== 'tree')) {
      $this -> notFound();
    }

    return $this -> view -> render($response, 'pages/repositories/tree.twig', [
      'repository' => $this -> repository,
      'ref' => $ref,
      'tree' => $tree,
    ]);
  }

}
