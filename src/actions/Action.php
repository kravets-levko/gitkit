<?php

namespace Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\InvalidMethodException;
use Slim\Exception\NotFoundException;
use Slim\Container;

class Action {
  private $currentRequest;
  private $currentResponse;

  protected $container;

  public function __construct(Container $container) {
    $this -> container = $container;
  }

  public function __get($name) {
    return $this -> container -> get($name);
  }

  protected function beforeRequest(Request $request, Response $response, $args) {
    return $response;
  }

  protected function afterRequest(Request $request, Response $response, $args) {
    return $response;
  }

  public function notFound() {
    throw new NotFoundException($this -> currentRequest, $this -> currentResponse);
  }

  public function __invoke(Request $request, Response $response, $args) {
    $method = strtolower($request -> getMethod());
    if (method_exists($this, $method)) {
      $this -> currentRequest = $request;
      $this -> currentResponse = $response;
      try {
        $response = $this -> beforeRequest($request, $response, $args);
        $response = $this -> {$method}($request, $response, $args);
        $response = $this -> beforeRequest($request, $response, $args);
      } finally {
        $this -> currentRequest = null;
        $this -> currentResponse = null;
      }
      return $response;
    }
    throw new InvalidMethodException($request, $request -> getMethod());
  }
}
