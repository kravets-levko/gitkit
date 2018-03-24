<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Classes\HttpStreamAdapter;

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
      $stdout = $blob -> raw();
      return $response
        -> withHeader('Content-Type', $blob -> mime)
        -> withBody(new HttpStreamAdapter(
        // read
          function($length = -1) use ($stdout) {
            trigger_error("Read `${length}`", E_USER_NOTICE);
            return $stdout -> read($length);
          },
          // eof
          function() use ($stdout) {
            trigger_error("Eof", E_USER_NOTICE);
            return $stdout -> eof();
          },
          // close
          function() use ($stdout) {
            trigger_error("Close", E_USER_NOTICE);
            $stdout -> close();
          }
        ));
    }


    return $this -> render('repositories/blob', [
      'repository' => $this -> repository,
      'ref' => $ref,
      'blob' => $blob,
    ]);
  }

}
