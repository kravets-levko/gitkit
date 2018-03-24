<?php

namespace Actions\Settings;

use Actions\Action;
use \System\EnvFile;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class System extends Action {

  /**
   * @var EnvFile
   */
  private $env;

  public function beforeRequest(Request $request, Response $response, &$args) {
    $this -> env = new EnvFile($this -> config -> envFile);
    return $response;
  }

  public function post(Request $request, Response $response, $args) {
    $params = $request -> getParsedBody();

    if (count($params) > 0) {
      foreach ($params as $key => $value) {
        $this -> env -> set($key, $value, true);
      }
      $this -> env -> save();
    }

    return $response -> withRedirect($request -> getUri(), 302);
  }

  public function get(Request $request, Response $response, $args) {
    return $this -> render('settings/system', [
      'env' => $this -> env -> variables(),
    ]);
  }

}
