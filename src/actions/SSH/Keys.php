<?php

namespace Actions\SSH;

use \Actions\Action;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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

    $params = $request -> getParsedBody();
    if (isset($params['key'])) {
      $action = $params['action'];
      $sshKey = $params['key'];
      $lines = @file($config -> sshAuthorizedKeys, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if (!is_array($lines)) $lines = [];

      if (($action == 'delete') || ($action == 'remove')) {
        $lines = array_filter($lines, function($line) use ($sshKey) {
          return $line != $sshKey;
        });
      }

      if ($action == 'add') {
        $key = $this -> parseSSHKey($sshKey);
        $description = trim(@$params['description']);
        if ($description != '') $key -> description = $description;
        $lines[] = implode(' ', array_filter([$key -> type, $key -> key, $key -> description], 'strlen'));
      }

      $lines = array_unique($lines);
      $lines[] = '';
      @file_put_contents($config -> sshAuthorizedKeys, implode(PHP_EOL, $lines));
    }
    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response) {
    $config = $this -> container -> get('config');

    $keys = @file($config -> sshAuthorizedKeys, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($keys)) $keys = [];

    $keys = array_map([$this, 'parseSSHKey'], $keys);

    return $this -> view -> render($response, 'pages/ssh-keys.twig', [
      'keys' => $keys,
    ]);
  }

}
