<?php

namespace Actions\Repositories;

use Actions\Action as BaseAction;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Action extends BaseAction {

  /**
   * @var \Git\Repository
   */
  protected $repository;

  protected function beforeRequest(Request $request, Response $response, &$args) {
    $path = realpath($this -> config -> repositoriesRoot . '/' .
      $args['group'] . '/' . $args['name'] . '.git');
    if (!$path) $this -> notFound();

    $this -> repository = $this -> repositories -> getRepository($path);
    return parent::beforeRequest($request, $response, $args);
  }

  protected function afterRequest(Request $request, Response $response, $args) {
    $this -> repository = null;
    return $response;
  }

}
