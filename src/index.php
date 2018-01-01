<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
    'outputBuffering' => false,
  ],
]);

$app -> group('/{group}/{name}', function() {
  $this -> any('/commit/{commit}', Actions\Commit::class);
  $this -> any('/tree/{ref:[^:]*}[:{path:.*}]', Actions\Tree::class);
  $this -> any('/blob/{ref:[^:]*}[:{path:.*}]', Actions\Blob::class);
  $this -> any('', Actions\Tree::class);
});
$app -> any('/ssh-keys', Actions\SSHKeys::class);
$app -> any('/[{group}]', Actions\Home::class);

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
