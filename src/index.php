<?php

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
  ],
]);
$app -> get('/', Actions\Home::class);
$app -> get('/{group}/{name}', Actions\Repository::class);

$container = $app->getContainer();
$container['view'] = function() {
  return new \Slim\Views\Twig(__DIR__ . '/views/', [
    'cache' => false,
  ]);
};

$container['config'] = function() {
  return json_decode(file_get_contents(__DIR__ . '/config/default.json'));
};

$app -> run();
