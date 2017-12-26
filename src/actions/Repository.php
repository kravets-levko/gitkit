<?php

namespace Actions;

use Classes\Action;
use Classes\Git\Branch;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Repository extends Action {

  private function parseBranchAndPath($branches, $url) {
    $branches = array_map(function(Branch $branch) {
      return $branch -> getName();
    }, $branches);
    usort($branches, function($a, $b) {
      return strlen($b) - strlen($a);
    });
    foreach ($branches as $branch) {
      if (($branch == $url) || (strpos($url, $branch . '/') === 0)) {
        return [$branch, trim(substr($url, strlen($branch) + 1), '/')];
      }
    }
    return [$url, ''];
  }

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = \Classes\Git\Repository::getRepository(
      $args['group'] . '/' . $args['name'], $config);

    $path = '';
    if (isset($args['branch'])) {
      list($branch, $path) = $this -> parseBranchAndPath($repo -> getBranches(), $args['branch']);
      $branch = $repo -> getBranch($branch);
    } else {
      $branch = $repo -> getDefaultBranch();
    }
    if (!$branch) {
      $this -> notFound();
    }
    if (!is_string($path)) {
      $path = '';
    }


    $parentPath = explode('/', $path);
    array_pop($parentPath);
    $parentPath = implode('/', $parentPath);

    return $this -> view -> render($response, 'pages/repository.twig', [
      'group' => $args['group'],
      'name' => $args['name'],
      'repository' => $repo,
      'branch' => $branch,
      'path' => $path,
      'parentPath' => $parentPath,
    ]);
  }

}
