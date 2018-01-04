<?php

namespace Classes\SSH;

use Classes\Properties;
use Classes\Process\{ Binary, StdPipe };

class PublicKey {
  use Properties;

  private $_fingerprintAlgorithm = 'md5';
  private $_valid = null;
  private $_raw = null;
  private $_fingerprint = null;
  private $_algorithm = null;
  private $_key = null;
  private $_comment = null;

  /**
   * @var Binary
   */
  public $keygen = null;

  private function parse() {
    if ($this -> _valid === null) {
      $this -> _valid = false;

      $stdin = fopen('data://text/plain;base64,' . base64_encode($this -> raw), 'r');
      $process = $this -> keygen -> start([
        '-l', '-E', $this -> _fingerprintAlgorithm, '-f', '-'
      ], null, null, [
        StdPipe::STDIN => $stdin,
      ]);

      $stdout = $process -> stdout() -> read();
      $exitCode = $process -> close();

      if ($exitCode == 0) {
        $this -> _valid = true;
        list(, $fingerprint) = explode(' ', $stdout);
        list(, $fingerprint) = explode(':', $fingerprint, 2);
        $this -> _fingerprint = $fingerprint;

        $raw = preg_replace('#\s+#', ' ', trim($this -> raw));
        list($algorithm, $key, $comment) = explode(' ', $raw, 3);
        $this -> _algorithm = strtolower($algorithm);
        $this -> _key = $key;
        $this -> _comment = $comment;
      }
    }
  }

  protected function get_valid() {
    $this -> parse();
    return $this -> _valid;
  }

  protected function get_raw() {
    return $this -> _raw;
  }

  protected function get_algorithm() {
    $this -> parse();
    return $this -> _algorithm;
  }

  protected function get_key() {
    $this -> parse();
    return $this -> _key;
  }

  protected function get_comment() {
    $this -> parse();
    return $this -> _comment;
  }

  protected function get_fingerprint() {
    $this -> parse();
    return $this -> _fingerprint;
  }

  public function __construct(string $raw, Binary $keygen, $fingerprintAlgorithm = 'md5') {
    $this -> _raw = trim($raw);
    $this -> _fingerprintAlgorithm = $fingerprintAlgorithm;
    $this -> keygen = $keygen;
  }

  public function __toString() {
    $this -> parse();
    return implode(' ', array_filter(array_map('trim', [
      $this -> algorithm,
      $this -> key,
      $this -> comment,
    ]), 'strlen'));
  }

}
