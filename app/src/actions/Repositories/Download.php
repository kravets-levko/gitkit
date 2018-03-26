<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Classes\HttpStreamAdapter;

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

    $stdout = $ref -> export($args['type']);
    return $response
      -> withHeader('Content-Type', 'application/zip')
      -> withBody(new HttpStreamAdapter(
        // read
        function($length = -1) use ($stdout) {
          return $stdout -> read($length);
        },
        // eof
        function() use ($stdout) {
          return $stdout -> eof();
        },
        // close
        function() use ($stdout) {
          $stdout -> close();
        }
      ));
  }

}
