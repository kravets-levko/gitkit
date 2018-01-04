<?php

namespace Actions\Repositories;

use Actions\Action;
use Classes\Git\Repository as GitRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Commits
 *
 * @property \Slim\Views\Twig $view
 */
class Commits extends Action {

  public function get(Request $request, Response $response, $args) {
    $config = $this -> container -> get('config');
    $repo = GitRepository::getRepository($args['group'] . '/' . $args['name'], $config);

    if (isset($args['ref'])) {
      $ref = $repo -> ref($args['ref']);
    } else {
      $ref = $repo -> defaultBranch;
    }
    if (!$ref) {
      $this -> notFound();
    }

    $query = $request -> getQueryParams();
    list($hash, $count) = explode(' ', $query['from']);
    $count = (int)trim($count);
    if ($count <= 0) $count = 30;

    $hash = strtolower(trim($hash));
    $commits = $ref -> commits;
    $index = 0;
    foreach ($commits as $i => $c) {
      if ($c -> hash == $hash) {
        $index = $i;
        break;
      }
    }

    $commits = array_slice($ref -> commits, $index, $count);
    $groups = [];
    foreach ($commits as $c) {
      $date = new \DateTime('@' . $c -> info -> committerDate);
      $date = $date -> format('M d, Y');
      @$groups[$date][] = $c;
    }

    $prev = @$ref -> commits[$index - $count];
    if (!$prev && ($index > 0)) $prev = $ref -> head;

    $next = @$ref -> commits[$index + $count];

    return $this -> view -> render($response, 'pages/commits.twig', [
      'repository' => $repo,
      'ref' => $ref,
      'groups' => $groups,
      'prev' => $prev,
      'next' => $next,
      'count' => $count,
    ]);
  }

}
