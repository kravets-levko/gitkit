<?php

namespace Classes\Git\Utils;

class Parse {

  static public function parseBranchList(string $str) {
    $default = null;
    $branches = [];

    $names = explode("\n", $str);
    foreach ($names as $name) {
      $name = explode(' ', trim($name));
      $isDefault = count($name) > 1;
      $name = end($name);
      if ($name != '') {
        $branches[] = $name;
        if ($isDefault && ($default === null)) $default = $name;
      }
    }

    sort($branches);

    return [$branches, $default];
  }

  static public function parseTagList(string $str) {
    $names = explode("\n", $str);
    $names = array_filter(array_map('trim', $names), 'strlen');
    ksort($names);
    return array_reverse($names);
  }

}
