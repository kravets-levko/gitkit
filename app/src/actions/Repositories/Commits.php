<?php

namespace Actions\Repositories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Class Commits
 *
 * @property \Slim\Views\Twig $view
 */
class Commits extends Action {

  public function get(Request $request, Response $response, $args) {
    if (isset($args['ref'])) {
      $ref = $this -> repository -> ref($args['ref']);
    } else {
      $ref = $this -> repository -> defaultBranch;
    }
    if (!$ref) {
      $this -> notFound();
    }

    $query = $request -> getQueryParams();
    @list($hash, $count) = explode(' ', trim(@$query['from']));
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
      $date = $c -> info -> committerDate -> format('M d, Y');
      @$groups[$date][] = $c;
    }

    $prev = @$ref -> commits[$index - $count];
    if (!$prev && ($index > 0)) $prev = $ref -> head;

    $next = @$ref -> commits[$index + $count];

    return $this -> view -> render($response, 'pages/repositories/commits.twig', [
      'repository' => $this -> repository,
      'ref' => $ref,
      'groups' => $groups,
      'prev' => $prev,
      'next' => $next,
      'count' => $count,
    ]);
  }

}
