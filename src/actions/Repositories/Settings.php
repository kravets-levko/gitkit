<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Settings
 *
 * @property \Slim\Views\Twig $view
 */
class Settings extends Action {

  public function post(Request $request, Response $response) {
    $params = $request -> getParsedBody();

    switch($params['action']) {
      case 'rename':
        $this -> repository -> name = $params['name'];
        $request = $request -> withUri(
          $request -> getUri() -> withPath($this -> repository -> name . '/settings')
        );
        break;
      case 'update_description':
        $this -> repository -> description = trim($params['description']);
        break;
      case 'set_default_branch':
        $branch = $this -> repository -> branch($params['default_branch']);
        if ($branch) $this -> repository -> defaultBranch = $branch;
        break;
      case 'delete':
        $this -> repository -> delete();
        $request = $request -> withUri($request -> getUri() -> withPath('/'));
        break;
    }

    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response) {
    return $this -> view -> render($response, 'pages/repositories/settings.twig', [
      'repository' => $this -> repository,
    ]);
  }

}
