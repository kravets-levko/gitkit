<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
  ],
]);

$app -> get('/{group}/{name}/tree/{branch}', Actions\Repository::class);
$app -> get('/{group}/{name}', Actions\Repository::class);
$app -> get('/', Actions\Home::class);

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
