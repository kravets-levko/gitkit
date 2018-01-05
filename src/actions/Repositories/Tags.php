<?php

namespace Actions\Repositories;

use Actions\Action;
use Classes\Git\Repository as GitRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Branches
 *
 * @property \Slim\Views\Twig $view
 */
class Tags extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = GitRepository::getRepository($args['group'] . '/' . $args['name'], $config);
    return $this -> view -> render($response, 'pages/tags.twig', [
      'repository' => $repo,
    ]);
  }

}
