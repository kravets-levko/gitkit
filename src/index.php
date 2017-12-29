<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
    'outputBuffering' => false,
  ],
]);

$app -> group('/{group}/{name}', function() {
  $this -> get('/commit/{commit}', Actions\Commit::class);
  $this -> get('/tree/{ref:[^:]*}[:{path:.*}]', Actions\Tree::class);
  $this -> get('/blob/{ref:[^:]*}[:{path:.*}]', Actions\Blob::class);
  $this -> get('', Actions\Tree::class);
});
$app -> get('/', Actions\Home::class);

$container = $app->getContainer();
$container['view'] = function() {
  $view = new \Slim\Views\Twig(__DIR__ . '/views/', [
    'cache' => false,
  ]);

  $view -> addExtension(new \Classes\Twig\Functions());

  return $view;
};

$container['config'] = function() {
  return json_decode(file_get_contents(__DIR__ . '/config/default.json'));
};

$app -> run();
