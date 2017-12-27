<?php

namespace Classes\Git\Core;

use \Classes\Git\Repository;

class Diff {

  private $repository;
  private $from;
  private $to;

  private $stats = null;

  private function getRange() {
    $from = '';
    $to = '';
    if ($this -> from instanceof Commit) {
      $from = $this -> from -> getHash();
    }
    if ($this -> to instanceof Commit) {
      $to = $this -> to -> getHash();
    }

    return implode('..', array_filter([$from, $to], 'strlen'));
  }

  public function __construct(Repository $repository, $from, $to = null) {
    $this -> repository = $repository;
    $this -> from = is_object($from) ? $from : null;
    $this -> to = is_object($to) ? $to : null;
  }

  public function getStats($slots = 7) {
    if ($this -> stats === null) {
      $data = $this -> repository -> exec([
        'show', '--format=format:', '--numstat', $this -> getRange(),
      ]);
      $data = array_filter(explode("\n", $data), 'strlen');

      $totalAdditions = 0;
      $totalDeletions = 0;
      $items = array_map(function($line) use (&$totalAdditions, &$totalDeletions) {
        list($additions, $deletions, $filename) = explode("\t", $line);
        $binary = ($additions == '-') || ($deletions == '-');
        $additions = (int)$additions;
        $deletions = (int)$deletions;

        $totalAdditions += $additions;
        $totalDeletions += $deletions;

        return (object)[
          'filename' => $filename,
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

  public function getDiff() {
    // TODO: Implement
    return '';
  }

  public function getPatch() {
    // TODO: Implement
    return '';
  }

}
