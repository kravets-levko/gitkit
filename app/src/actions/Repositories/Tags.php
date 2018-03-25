<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Tags extends Action {

  public function post(Request $request, Response $response) {
    $params = $request -> getParsedBody();

    switch($params['action']) {
      case 'delete':
        $tag = $this -> repository -> tag($params['tag']);
        if ($tag) $tag -> delete();
        break;
    }

    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response, $args) {
    if (count($this -> repository -> tags) == 0) {
      $this -> notFound();
    }

    return $this -> render('repositories/tags', [
      'repository' => $this -> repository,
    ]);
  }

}
