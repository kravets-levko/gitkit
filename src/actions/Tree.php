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
class Tree extends Action {

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
    $path = isset($args['path']) && is_string($args['path']) ? $args['path'] : '';

    $parentPath = explode('/', $path);
    array_pop($parentPath);
    $parentPath = implode('/', $parentPath);

    return $this -> view -> render($response, 'pages/tree.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'ref' => $ref,
      'path' => $path,
      'parentPath' => $parentPath,
    ]);
  }

}
