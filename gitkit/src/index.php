<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/utils.php';

define('GITKIT_VERSION', trim(file_get_contents(__DIR__ . '/../VERSION')));
$_SERVER['GITKIT_VERSION'] = GITKIT_VERSION;

$app = new \Slim\App([
  'settings' => [
    'displayErrorDetails' => true,
    'outputBuffering' => false,
    'determineRouteBeforeAppMiddleware' => true,
  ],
]);

new \Actions\SSH\Router($app);
new \Actions\Repositories\Router($app);

$container = $app -> getContainer();

$container['version'] = GITKIT_VERSION;

$container['config'] = (new \Utils\Config\Environment('gitkit'))
  -> extendWith([
    'version' => GITKIT_VERSION,
    'theme' => 'github',
    'sshFingerprintAlgorithm' => 'md5',
  ]);

$container['view'] = function($container) {
  $theme = $container -> config -> theme;

  $view = new \Slim\Views\Twig(__DIR__ . "/themes/{$theme}/templates/", [
    'cache' => false,
  ]);

  $view['assetsPath'] = "/themes/{$theme}";
  $view['version'] = GITKIT_VERSION;

  $view -> addExtension(new \Classes\Twig\Functions());

  return $view;
};

$container['repositories'] = function($container) {
  return new \Git\Repositories($container -> config);
};

$app -> run();
