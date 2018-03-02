<?php

namespace Actions\SSH;

use Actions\Action;
use SSH\AuthorizedKeys;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Repository
 *
 * @property \Slim\Views\Twig $view
 */
class Keys extends Action {

  protected function parseSSHKey($str) {
    list($type, $key, $description) = array_map('trim', explode(' ', $str, 3));
    return (object)[
      'type' => $type,
      'key' => $key,
      'description' => $description,
      'raw' => $str,
    ];
  }

  protected function beforeRequest(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    if (!isset($config -> sshAuthorizedKeys)) $this -> notFound();
    return parent::beforeRequest($request, $response, $args);
  }

  public function post(Request $request, Response $response) {
    $config = $this -> container -> get('config');
    $keys = new AuthorizedKeys($config);
    $params = $request -> getParsedBody();

    if (isset($params['key'])) {
      $action = $params['action'];
      $sshKey = $params['key'];

      if (($action == 'delete') || ($action == 'remove')) {
        $keys -> remove($sshKey);
        $keys -> save();
      } elseif ($action == 'add') {
        $keys -> add($sshKey);
        $keys -> save();
      } elseif ($action == 'create') {
        // TODO: Implement using AuthorizedKeys::create()
      }
    }
    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response) {
    $config = $this -> container -> get('config');
    $keys = new AuthorizedKeys($config);
    return $this -> view -> render($response, 'pages/ssh/keys.twig', [
      'keys' => $keys -> items,
    ]);
  }

}
