<?php

namespace Actions\Settings;

use Actions\Action;
use SSH\{ AuthorizedKeys, InvalidPublicKey };
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Keys extends Action {

  protected function beforeRequest(Request $request, Response $response, &$args) {
    $config = $this -> container -> get('config');
    if (!isset($config -> sshAuthorizedKeys)) $this -> notFound();
    return parent::beforeRequest($request, $response, $args);
  }

  public function post(Request $request, Response $response, $args) {
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
        try {
          $keys -> add($sshKey);
          $keys -> save();
        } catch (InvalidPublicKey $e) {
          return $this -> get($request, $response, array_merge($args, [
            'error' => 'Cannot add public key: ' . $e -> getMessage(),
          ]));
        }
      } elseif ($action == 'create') {
        // TODO: Implement using AuthorizedKeys::create()
      }
    }
    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $keys = new AuthorizedKeys($config);
    return $this -> render('settings/keys', [
      'keys' => $keys -> items,
      'error' => @$args['error'],
    ]);
  }

}
