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
class Repository extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);

    $path = '';
    if (isset($args['branch'])) {
      list($ref, $path) = explode(':', $args['branch']);
      $ref = $repo -> ref($ref);
    } else {
      $ref = $repo -> defaultBranch;
    }
    if (!$ref) {
      $this -> notFound();
    }
    if (!is_string($path)) {
      $path = '';
    }

    $parentPath = explode('/', $path);
    array_pop($parentPath);
    $parentPath = implode('/', $parentPath);

    return $this -> view -> render($response, 'pages/repository.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'ref' => $ref,
      'path' => $path,
      'parentPath' => $parentPath,
    ]);
  }

}
