<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
    'outputBuffering' => false,
    'determineRouteBeforeAppMiddleware' => true,
  ],
]);

new \Actions\SSH\Router($app);
new \Actions\Repositories\Router($app);


$container = $app->getContainer();
$container['view'] = function() {
  $view = new \Slim\Views\Twig(__DIR__ . '/views/', [
    'cache' => false,
  ]);

  $view -> addExtension(new \Classes\Twig\Functions());

  return $view;
};

$container['config'] = new \Classes\Config();

$app -> run();
