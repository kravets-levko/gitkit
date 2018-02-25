<?php

namespace SSH;

use Utils\Mixin\{ Properties, Cached };

class AuthorizedKeys {
  use Properties;
  use Cached;

  public $context;

  private function removeKey($items, $key) {
    return array_filter(
      $items,
      function(PublicKey $item) use ($key) {
        $key = $key -> parsed;
        $item = $item -> parsed;
        return ($item -> algorithm !== $key -> algorithm) || ($item -> key !== $key -> key);
      }
    );
  }

  protected function get_items() {
    return $this -> cached('items', function() {
      $lines = @file_get_contents($this -> context -> config -> sshAuthorizedKeys);
      if (!is_string($lines)) $lines = '';

      $lines = explode("\n", $lines);
      $lines = array_filter(array_map('trim', $lines), 'strlen');

      return array_map(function($line) {
        return new PublicKey($this -> context, $line);
      }, $lines);
    });
  }

  public function __construct($path, $config) {
    $this -> context = new Context($this -> _config);
  }

  public function create($algorithm, $bits, $comment = '', $passphrase = '') {
    $filename = tempnam(null, sha1(microtime() . ' ' . uniqid()));
    $filenamePub = $filename . '.pub';

    list($status, , $stderr) = $this -> context -> keygen -> execute([
      '-t', $algorithm, '-b', $bits, '-f', $filename, '-q',
      '-C', $comment, '-N', $passphrase
    ]);

    if ($status != 0) throw new Exception($stderr);

    // Hide possible notices to avoid accidental leak of filenames
    $result = [
      @file_get_contents($filename),
      @file_get_contents($filenamePub),
    ];

    @unlink($filename);
    @unlink($filenamePub);

    return $result;
  }

  /**
   * @param PublicKey | string $key
   */
  public function add($key) {
    if (!($key instanceof PublicKey)) {
      $key = new PublicKey($this -> context, $key);
    }
    $items = $this -> removeKey($this -> items, $key);
    $items[] = $key;
    $this -> cachedSet('items', $items);
  }

  /**
   * @param PublicKey | string $key
   */
  public function remove($key) {
    if (!($key instanceof PublicKey)) {
      $key = new PublicKey($this -> context, $key);
    }
    $items = $this -> removeKey($this -> items, $key);
    $this -> cachedSet('items', $items);
  }

  public function save() {
    @file_put_contents($this -> context -> config -> sshAuthorizedKeys,
      implode(PHP_EOL, $this -> items) . PHP_EOL, LOCK_EX);
  }

}
