<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
  ],
]);

$app -> group('/{group}/{name}', function() {
  $this -> get('/commit/{commit}', Actions\Commit::class);
  $this -> get('/tree/{branch:.*}', Actions\Repository::class);
  $this -> get('', Actions\Repository::class);
});
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
