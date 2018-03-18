<?php

namespace Actions\Repositories;

use Actions\Action;
use Models\Repositories\Create as CreateModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class View
 *
 * @property \Slim\Views\Twig $view
 */
class Create extends Action {

  public function post(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $model = new CreateModel($request -> getParsedBody());
    if ($model -> valid()) {
      $path = $config -> repositoriesRoot . '/' . $model -> get('group') . '/' .
        $model -> get('name') . '.git';
      $repository = $this -> repositories -> createRepository($path);
      return $response -> withRedirect('/' . $repository -> name, 302);
    }

    return $this -> get($request, $response, array_merge($args, [
      'model' => $model,
    ]));
  }

  public function get(Request $request, Response $response, $args) {
    $model = @$args['model'];
    if (!$model) {
      $model = new CreateModel([
        'group' => $request -> getParam('group'),
      ]);
    }

    $repos = $this -> repositories -> getRepositories();
    $groups = [];
    foreach ($repos as $repo) {
      list($repositoryGroup) = explode('/', $repo -> name);
      @$groups[$repositoryGroup] += 1;
    }
    ksort($groups);

    return $this -> view -> render($response, 'pages/repositories/create.twig', [
      'groups' => $groups,
      'model' => $model,
    ]);
  }

}
