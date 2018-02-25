<?php

namespace SSH;

use Utils\Mixin\Properties;
use Utils\Mixin\Cached;
use Process\StdPipe;

class PublicKey {
  use Properties;
  use Cached;

  private $_raw;

  public $context;

  protected function get_parsed() {
    return $this -> cached(__METHOD__, function() {
      $result = (object)[
        'valid' => false,
        'algorithm' => null,
        'key' => null,
        'comment' => null,
      ];

      try {
        // validate key
        $this -> fingerprint();

        // parse (if valid)
        list($algorithm, $key, $comment) = preg_split('#\s+#', ' ', trim($this -> raw), 3);
        $result -> algorithm = strtolower(trim($algorithm));
        $result -> key = trim($key);
        $result -> comment = trim($comment);
      } catch (Exception $e) {
        $result -> valid = false;
      }

      return $result;
    });
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
    return $this -> fingerprint();
  }

  public function __construct(Context $context, string $raw) {
    $this -> context = $context;
    $this -> _raw = trim($raw);
  }

  public function __toString() {
    return trim(implode(' ', [
      $this -> algorithm,
      $this -> key,
      $this -> comment,
    ]));
  }

  public function fingerprint($algorithm = 'md5') {
    return $this -> cached([__METHOD__, $algorithm], function() use ($algorithm) {
      $stdin = fopen('data://text/plain,' . $this -> raw, 'r');
      $process = $this -> context -> keygen -> start([
        '-l', '-E', $algorithm, '-f', '-'
      ], [
        StdPipe::STDIN => $stdin,
      ]);

      $stdout = $process -> stdout() -> read();
      $exitCode = $process -> close();

      if ($exitCode != 0) throw new Exception(
        "Cannot create public key fingerprint using '${algorithm}' algorithm"
      );

      list(, $fingerprint) = explode(' ', $stdout);
      list(, $fingerprint) = explode(':', $fingerprint, 2);
      return $fingerprint;
    });
  }

}
