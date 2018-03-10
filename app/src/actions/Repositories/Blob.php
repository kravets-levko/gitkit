<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Blob
 *
 * @property \Slim\Views\Twig $view
 */
class Blob extends Action {

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
     * @var \Git\Blob $blob
     */
    $blob = $ref -> tree -> node($path, true);
    if (!$blob || ($blob -> type !== 'blob')) {
      $this -> notFound();
    }

    // Raw output
    if (array_key_exists('raw', $request -> getQueryParams())) {
      if (!headers_sent()) {
        header("Content-Type: {$blob -> mime}", true);
      }
      // TODO: do not read entire stream
      echo $blob -> displayData() -> read();
      die;
    }


    return $this -> view -> render($response, 'pages/repositories/blob.twig', [
      'repository' => $this -> repository,
      'ref' => $ref,
      'blob' => $blob,
    ]);
  }

}
