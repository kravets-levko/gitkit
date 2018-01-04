<?php

function array_first($array) {
  return reset($array);
}

function array_last($array) {
  return end($array);
}

function prepare_string_list($list, $default = ['*'], $allowEmpty = false) {
  if (count($list) == 0) {
    $list = is_array($default) ? $default : [$default];
  } elseif ((count($list) == 1) && is_array($list[0])) {
    $list = $list[0];
  }
  $list = array_filter($list, 'is_string');
  if (!$allowEmpty) {
    $list = array_filter($list, 'strlen');
  }
  return $list;
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

function parse_mime($mime) {
  $pattern = '#^(?<type>[^/+]+)/(?<subtype>[^/+]+)([+](?<suffix>[^/+]+))?(;.*)?$#';
  if (preg_match($pattern, strtolower($mime), $matches)) {
    return array_intersect_key($matches, [
      'type' => true,
      'subtype' => true,
      'suffix' => true,
    ]);
  }
  return false;
}

function matches_mime($subject, $pattern) {
  $subject = parse_mime($subject);
  if (!$subject) return false;

  $pattern = parse_mime($pattern);
  if (!$pattern) return false;

  // if pattern type is not wildcard, it must match exactly
  if (($pattern['type'] != '*') && ($subject['type'] != $pattern['type'])) {
    return false;
  }

  // if pattern has suffix, then subject also should have it
  // if pattern suffix is not wildcard, it must match exactly
  if (array_key_exists('suffix', $pattern)) {
    if (!array_key_exists('suffix', $subject)) {
      return false;
    }
    if (($pattern['suffix'] != '*') && ($subject['suffix'] != $pattern['suffix'])) {
      return false;
    }
  }

  // if pattern subtype is not wildcard, it must match exactly
  if (($pattern['subtype'] != '*') && ($subject['subtype'] != $pattern['subtype'])) {
    return false;
  }

  return true;
}

function format_bytes($size, $precision = 2) {
  $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
  $step = 1024;
  $i = 0;
  while (($size / $step) > 0.9) {
    $size = $size / $step;
    $i++;
  }
  return round($size, $precision).$units[$i];
}
