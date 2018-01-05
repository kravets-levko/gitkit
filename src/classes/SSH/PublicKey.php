<?php

namespace Classes\SSH;

use Classes\Properties;
use Classes\Process\{ Binary, StdPipe };

class PublicKey {
  use Properties;

  private $_fingerprintAlgorithm = 'md5';
  private $_raw = null;

  /**
   * @var Binary
   */
  public $keygen = null;

  protected function cached_parsed() {
    $result = (object)[
      'valid' => false,
      'algorithm' => null,
      'key' => null,
      'comment' => null,
      'fingerprint' => null,
    ];

    $stdin = fopen('data://text/plain;base64,' . base64_encode($this -> raw), 'r');
    $process = $this -> keygen -> start([
      '-l', '-E', $this -> _fingerprintAlgorithm, '-f', '-'
    ], [
      StdPipe::STDIN => $stdin,
    ]);

    $stdout = $process -> stdout() -> read();
    $exitCode = $process -> close();

    if ($exitCode == 0) {
      $result -> valid = true;

      list(, $fingerprint) = explode(' ', $stdout);
      list(, $fingerprint) = explode(':', $fingerprint, 2);
      $result -> fingerprint = $fingerprint;

      $raw = preg_replace('#\s+#', ' ', trim($this -> raw));
      list($algorithm, $key, $comment) = explode(' ', $raw, 3);
      $result -> algorithm = strtolower($algorithm);
      $result -> key = $key;
      $result -> comment = $comment;
    }

    return $result;
  }

  protected function get_valid() {
    return $this -> parsed -> valid;
  }

  protected function get_raw() {
    return $this -> _raw;
  }

  protected function get_algorithm() {
    return $this -> parsed -> algorithm;
  }

  protected function get_key() {
    return $this -> parsed -> key;
  }

  protected function get_comment() {
    return $this -> parsed -> comment;
  }

  protected function get_fingerprint() {
    return $this -> parsed -> fingerprint;
  }

  public function __construct(string $raw, Binary $keygen, $fingerprintAlgorithm = 'md5') {
    $this -> _raw = trim($raw);
    $this -> _fingerprintAlgorithm = $fingerprintAlgorithm;
    $this -> keygen = $keygen;
  }

  public function __toString() {
    return implode(' ', array_filter(array_map('trim', [
      $this -> algorithm,
      $this -> key,
      $this -> comment,
    ]), 'strlen'));
  }

}
