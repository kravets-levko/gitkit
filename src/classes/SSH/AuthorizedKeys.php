<?php

namespace Classes\SSH;

use Classes\Properties;
use Classes\Process\Binary;

/**
 * Class AuthorizedKeys
 *
 * @property-read PublicKey[] $items
 */
class AuthorizedKeys {
  use Properties;

  /**
   * @var \stdClass
   */
  private $_config;
  private $_path;

  /**
   * @var PublicKey[]
   */
  private $_items = null;

  /**
   * @var Binary
   */
  public $keygen = null;

  protected function getItems() {
    if ($this -> _items === null) {
      $lines = @file_get_contents($this -> _path);
      if (!is_string($lines)) $lines = '';
      $lines = explode("\n", $lines);

      $lines = array_filter(array_map('trim', $lines), 'strlen');
      $this -> _items = array_map(function($line) {
        return new PublicKey($line, $this -> keygen);
      }, $lines);
    }
    return $this -> _items;
  }

  public function __construct($path, $config) {
    $this -> _config = $config;
    $this -> _path = $path;
    $this -> keygen = new Binary($this -> _config -> sshKeygenBinary);
  }

  public function create($algorithm, $bits, $comment = '', $passphrase = '') {
    $filename = tempnam(null, sha1(microtime() . ' ' . uniqid()));
    $filenamePub = $filename . '.pub';

    list($status, , $stderr) = $this -> keygen -> execute([
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
    if ($key instanceof PublicKey) {
      $key = $key -> raw;
    }
    $this -> getItems();
    $this -> _items[] = new PublicKey($key, $this -> keygen);
  }

  /**
   * @param PublicKey | string $key
   */
  public function remove($key) {
    if ($key instanceof PublicKey) {
      $key = $key -> raw;
    }
    $this -> getItems();
    $this -> _items = array_filter(
      $this -> _items,
      function(PublicKey $item) use ($key) {
        return $item -> raw != $key;
      }
    );
  }

  public function save() {
    $this -> getItems();
    @file_put_contents($this -> _path, implode(PHP_EOL, $this -> items) . PHP_EOL, LOCK_EX);
  }

}
