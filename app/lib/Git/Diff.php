<?php

namespace Git;

class Diff {

  private $context;
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

  private function getDiffRanges($diff) {
    $result = [];

    $lines = explode("\n", $diff);
    $pattern = '#^\s*@@\s*\-(?<del>[0-9]+(,[0-9]+)?)\s*\+(?<add>[0-9]+(,[0-9]+)?)\s*@@#u';
    while (count($lines) > 0) {
      $line = array_shift($lines);
      if (preg_match($pattern, $line, $matches)) {
        list($delOffset, $delCount) = explode(',', $matches['del'] . ',1');
        list($addOffset, $addCount) = explode(',', $matches['add'] . ',1');
        $delLines = [];
        $addLines = [];
        while (count($lines) > 0) {
          $line = array_shift($lines);
          if (preg_match($pattern, $line)) {
            array_unshift($lines, $line);
            break;
          }
          $type = substr($line, 0, 1);
          $line = substr($line, 1);
          switch ($type) {
            case '-':
              $delLines[] = $line;
              break;
            case '+':
              $addLines[] = $line;
              break;
          }
        }

        $result[] = (object)[
          'delete' => (object)[
            'offset' => (int)$delOffset,
            'count' => (int)$delCount,
            'lines' => $delLines,
          ],
          'add' => (object)[
            'offset' => (int)$addOffset,
            'count' => (int)$addCount,
            'lines' => $addLines,
          ],
        ];
      }
    }

    return $result;
  }

  public function __construct(RepositoryContext $context, Commit $from, Commit $to = null) {
    $this -> context = $context;
    $this -> _from = is_object($from) ? $from : null;
    $this -> _to = is_object($to) ? $to : null;
  }

  public function stats($slots = 7) {
    if ($this -> stats === null) {
      $data = $this -> context -> execute([
        'show', '--format=format:', '--numstat', $this -> getRange(), '--',
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

  public function get($path) {
    $data = $this -> context -> execute([
      'show', '--no-expand-tabs', '--unified=0', '--format=format:%b',
      $this -> getRange(), '--', $path,
    ]);

    $commit = $this -> _to ? $this -> _to : $this -> _from;
    $blob = $commit -> tree -> node($path);
    $blob -> info -> diff = $this -> getDiffRanges($data);

    return $blob;
  }

  public function diff() {
    return $this -> context -> execute([
      'show', '--no-expand-tabs', '--format=format:%b', $this -> getRange(), '--',
    ], true);
  }

  public function patch() {
    return $this -> context -> execute([
      'show', '--no-expand-tabs', '--format=email', '--patch', '--stat', $this -> getRange(), '--',
    ], true);
  }

}
