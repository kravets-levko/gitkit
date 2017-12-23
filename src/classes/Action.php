<?php

namespace Classes;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Exception\InvalidMethodException;
use \Slim\Container;

class Action {
  protected $container;

  public function __construct(Container $container) {
    $this -> container = $container;
  }

  public function __get($name) {
    return $this -> container -> get($name);
  }

  public function __invoke(Request $request, Response $response, $args) {
    $method = strtolower($request -> getMethod());
    if (method_exists($this, $method)) {
      return $this -> {$method}($request, $response, $args);
    }
    throw new InvalidMethodException($request, $request -> getMethod());
  }
}
