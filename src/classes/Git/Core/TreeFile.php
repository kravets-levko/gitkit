<?php

namespace Classes\Git\Core;

class TreeFile extends Blob {

  protected function get_data() {
    return $this -> repository -> exec(
      'show',
      $this -> commit -> hash . ':' . $this -> path
    );
  }

  protected function get_ext() {
    $result = pathinfo($this -> path, PATHINFO_EXTENSION);
    return is_string($result) ? $result : '';
  }

  protected function get_mime() {
    return media_type_from_filename($this -> path);
  }

  public function matchesMime(...$mimeTypes) {
    $mimeTypes = prepare_string_list($mimeTypes, []);
    foreach ($mimeTypes as $pattern) {
      if (matches_mime($this -> mime, $pattern)) {
        return true;
      }
    }
    return false;
  }

  public function displayData() {
    $this -> repository -> passthru(
      'show',
      $this -> commit -> hash . ':' . $this -> path
    );
  }

}
