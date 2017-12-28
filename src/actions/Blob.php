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
class Blob extends Action {

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

    /**
     * @var \Classes\Git\Core\TreeFile $blob
     */
    $blob = $ref -> tree -> node($path);
    if (!$blob || ($blob -> type !== 'blob')) {
      $this -> notFound();
    }

    // Raw output
    if (array_key_exists('raw', $request -> getQueryParams())) {
      if (!headers_sent()) {
        header("Content-Type: {$blob -> mime}", true);
      }
      $blob -> displayData();
      die;
    }


    return $this -> view -> render($response, 'pages/blob.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'ref' => $ref,
      'blob' => $blob,
    ]);
  }

}
