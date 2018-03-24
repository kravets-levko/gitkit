<?php

namespace Classes\Twig\Extension;

use Twig_Extension as TwigExtension;
use Twig_Filter as TwigFilter;

use Classes\Twig\TokenParser\Pre as TwigTokenParserPre;
use Classes\Twig\NodeVisitor\RelativePath as TwigNodeVisitorRelativePath;

class GitKit extends TwigExtension {

  private function prepareDateTime($date) {
    if (!$date instanceof \DateTimeInterface) {
      $date = (string)$date;
      if (preg_match('/^-?[0-9]+$/', $date)) $date = '@' . $date;
      $date = new \DateTime($date);
      $date -> setTimezone(new \DateTimeZone('UTC'));
    }
    return $date;
  }

  private function formatInterval($interval) {
    if ($interval -> y > 0) {
      return $interval -> y > 1 ? $interval -> y . ' years ago' : 'a year ago';
    } elseif ($interval -> m > 0) {
      return $interval -> m > 1 ? $interval -> m . ' months ago' : 'a month ago';
    } elseif ($interval -> d > 0) {
      return $interval -> d > 1 ? $interval -> d . ' days ago' : 'a day ago';
    } elseif ($interval -> h > 0) {
      return $interval -> h > 1 ? $interval -> h . ' hours ago' : 'an hour ago';
    } elseif ($interval -> i > 0) {
      return $interval -> i > 1 ? $interval -> i . ' minutes ago' : 'a minute ago';
    } else {
      return 'just now';
    }
  }

  public function getTokenParsers() {
    return [
      new TwigTokenParserPre(),
    ];
  }

  public function getNodeVisitors() {
    return [
      new TwigNodeVisitorRelativePath(),
    ];
  }

  public function getFilters() {
    return [
      new TwigFilter('md5', [$this, 'md5']),
      new TwigFilter('format_bytes', [$this, 'formatBytes']),
      new TwigFilter('format_time_ago', [$this, 'formatTimeAgo']),
      new TwigFilter('pretty_date', [$this, 'prettyDate']),
      new TwigFilter('full_date', [$this, 'fullDate']),
    ];
  }

  public function md5($value) {
    return md5($value);
  }

  public function formatBytes($value) {
    return format_bytes($value);
  }

  public function fullDate($date, $format = 'M d, Y, H:m e') {
    $date = $this -> prepareDateTime($date);
    return $date -> format($format);
  }

  public function formatTimeAgo($date) {
    $date = $this -> prepareDateTime($date);
    $now = new \DateTime('now');
    return $this -> formatInterval($now -> diff($date, true));
  }

  public function prettyDate($date, $absoluteTemplate = '%s', $intervalTemplate = '%s') {
    $date = $this -> prepareDateTime($date);
    $now = new \DateTime('now');

    $interval = $now -> diff($date, true);
    if ($interval -> d <= 5) {
      $template = $intervalTemplate;
      $result = $this -> formatInterval($interval);
    } else {
      $template = $absoluteTemplate;
      if ($date -> format('Y') == $now -> format('Y')) {
        $result = $date -> format('M d');
      } else {
        $result = $date -> format('M d, Y');
      }
    }

    return sprintf($template, $result);
  }

}
