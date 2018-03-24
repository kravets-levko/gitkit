<?php

namespace Actions\Repositories;

use Actions\Action;
use Models\Repositories\Create as CreateModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Create extends Action {

  public function post(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $model = new CreateModel($request -> getParsedBody());
    if ($model -> valid()) {
      $path = $config -> repositoriesRoot . '/' . $model -> get('group') . '/' .
        $model -> get('name') . '.git';
      $repository = $this -> repositories -> createRepository($path);
      $repository -> description = $model -> get('description');
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

    return $this -> render('repositories/create', [
      'groups' => $groups,
      'model' => $model,
    ]);
  }

}
