<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

class Diff {

  private $_repository;
  private $_from;
  private $_to;

  private $stats = null;

  private function getRange() {
    $from = '';
    $to = '';
    if ($this -> _from instanceof Commit) {
      $from = $this -> _from -> hash;
    }
    if ($this -> _to instanceof Commit) {
      $to = $this -> _to -> hash;
    }

    return implode('..', array_filter([$from, $to], 'strlen'));
  }

  public function __construct(Repository $repository, $from, $to = null) {
    $this -> _repository = $repository;
    $this -> _from = is_object($from) ? $from : null;
    $this -> _to = is_object($to) ? $to : null;
  }

  public function stats($slots = 7) {
    if ($this -> stats === null) {
      $data = $this -> _repository -> exec([
        'show', '--format=format:', '--numstat', $this -> getRange(),
      ]);
      $data = array_filter(explode("\n", $data), 'strlen');

      $totalAdditions = 0;
      $totalDeletions = 0;
      $items = array_map(function($line) use (&$totalAdditions, &$totalDeletions) {
        list($additions, $deletions, $path) = explode("\t", $line);
        $binary = ($additions == '-') || ($deletions == '-');
        $additions = (int)$additions;
        $deletions = (int)$deletions;

        $totalAdditions += $additions;
        $totalDeletions += $deletions;

        return (object)[
          'path' => $path,
          'name' => pathinfo($path, PATHINFO_BASENAME),
          'type' => 'blob',
          'binary' => $binary,
          'additions' => (int)$additions,
          'deletions' => (int)$deletions,
        ];
      }, $data);

      $this -> stats = (object)[
        'additions' => $totalAdditions,
        'deletions' => $totalDeletions,
        'items' => $items,
      ];
    }

    $max = 0;
    foreach ($this -> stats -> items as $item) {
      if (!$item -> binary) {
        if ($item -> additions + $item -> deletions > $max) {
          $max = $item -> additions + $item -> deletions;
        }
      }
    }

    $result = clone $this -> stats;
    $result -> items = array_map(function($item) use ($max, $slots) {
      if ($item -> binary) {
        $item -> additions_slots = 0;
        $item -> deletions_slots = 0;
      } else {
        $item -> additions_slots = max(
          round($item -> additions / $max * $slots),
          $item -> additions > 0 ? 1 : 0
        );
        $item -> deletions_slots = max(
          round($item -> deletions / $max * $slots),
          $item -> deletions > 0 ? 1 : 0
        );
      }
      return $item;
    }, $this -> stats -> items);

    return $result;
  }

  public function diff() {
    // TODO: Implement
    return '';
  }

  public function patch() {
    // TODO: Implement
    return '';
  }

}
