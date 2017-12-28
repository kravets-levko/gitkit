<?php

function array_first($array) {
  return reset($array);
}

function array_last($array) {
  return end($array);
}

function matches_glob($subject, $glob) {
  $pattern = '/^' . preg_replace_callback('/./', function($matches) {
    $c = $matches[0];
    switch ($c) {
      case '*': return '.*';
      case '?': return '.';
      case '[': return '[';
      case ']': return ']';
      default: return preg_quote($c, '/');
    }
  }, $glob) . '$/i';

  return preg_match($pattern, $subject);
}
