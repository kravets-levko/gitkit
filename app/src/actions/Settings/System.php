<?php

namespace Actions\Settings;

use Actions\Action;
use SSH\{ AuthorizedKeys, InvalidPublicKey };
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Repository
 *
 * @property \Slim\Views\Twig $view
 */
class System extends Action {

  public function post(Request $request, Response $response, $args) {
    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response, $args) {
    return $this -> view -> render($response, 'pages/settings/system.twig', []);
  }

}
