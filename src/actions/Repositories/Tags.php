<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Branches
 *
 * @property \Slim\Views\Twig $view
 */
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
    return $this -> view -> render($response, 'pages/repositories/tags.twig', [
      'repository' => $this -> repository,
    ]);
  }

}
