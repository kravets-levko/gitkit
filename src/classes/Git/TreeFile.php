<?php

namespace Classes\Git;

use Classes\Process\Process;

class TreeFile extends Blob {

  protected function get_data() {
    return $this -> repository -> git -> execute([
      'show',
      $this -> commit -> hash . ':' . $this -> path
    ]);
  }

  protected function get_ext() {
    $result = pathinfo($this -> path, PATHINFO_EXTENSION);
    return is_string($result) ? $result : '';
  }

  protected function cached_mime() {
    /**
     * @var Process $process
     */
    $process = $this -> repository -> git -> start([
      'show', $this -> commit -> hash . ':' . $this -> path
    ]);
    $process -> stdin() -> close();
    $process -> stderr() -> close();

    $process = new Process('file --brief --mime-type -', null, null, [
      0 => $process -> stdout(),
    ]);
    $result = strtolower(trim($process -> stdout() -> read()));
    $process -> close();

    if ($result == 'text/plain') {
      if (in_array(strtolower($this -> ext), ['md', 'markdown'])) {
        $result = 'text/markdown';
      }
    }

    return $result;
  }

  protected function cached_lines() {
    $result = (object)[
      'total' => 0,
      'nonEmpty' => 0,
    ];

    /**
     * @var Process $process
     */

    // Total lines
    $process = $this -> repository -> git -> start([
      'show', $this -> commit -> hash . ':' . $this -> path
    ]);
    $process -> stdin() -> close();
    $process -> stderr() -> close();

    $process = new Process('wc --lines -', null, null, [
      0 => $process -> stdout(),
    ]);
    $result -> total = (int)(trim($process -> stdout() -> read()));
    $process -> close();

    // Non-empty lines
    $process = $this -> repository -> git -> start([
      'show', $this -> commit -> hash . ':' . $this -> path
    ]);
    $process -> stdin() -> close();
    $process -> stderr() -> close();

    $process = new Process('grep --count --invert-match --line-regexp \'^\s*$\'', null, null, [
      0 => $process -> stdout(),
    ]);
    $result -> nonEmpty = (int)(trim($process -> stdout() -> read()));
    $process -> close();

    return $result;
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
    return $this -> repository -> git -> getOutputAsStream([
      'show',
      $this -> commit -> hash . ':' . $this -> path
    ]);
  }

}
