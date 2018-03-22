<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Download
 *
 * @property \Slim\Views\Twig $view
 */
class Download extends Action {

  protected function beforeRequest(Request $request, Response $response, &$args) {
    if (array_key_exists('ref', $args)) {
      $parsed = pathinfo($args['ref']);
      $args['type'] = strtolower($parsed['extension']);

      if ($parsed['dirname'] != '.') {
        $args['ref'] = $parsed['dirname'] . '/' . $parsed['filename'];
      } else {
        $args['ref'] = $parsed['filename'];
      }
    }
    return parent::beforeRequest($request, $response, $args);
  }

  public function get(Request $request, Response $response, $args) {
    if ($args['type'] != 'zip') {
      $this -> notFound();
    }

    $ref = null;
    if (isset($args['ref'])) {
      $ref = $this -> repository -> ref($args['ref']);
    }
    if (!$ref) {
      $this -> notFound();
    }

    if (!headers_sent()) {
      header('Content-Type: application/zip', true);
    }
    // TODO: do not read entire stream
    echo $ref -> export($args['type']) -> read();
    die;
  }

}
