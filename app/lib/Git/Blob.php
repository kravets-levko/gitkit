<?php

namespace Git;

use Process\{ Process, StdPipe };
use Utils\Mixin\{ Properties, Cached };

class Blob {
  use Properties;
  use Cached;

  protected $context;
  protected $_info;
  private $_ref;
  private $_parent;

  protected function get_parent() {
    return $this -> _parent;
  }

  protected function get_ref() {
    return $this -> _ref;
  }

  protected function get_info() {
    return $this -> _info;
  }

  protected function get_name() {
    return $this -> info -> name;
  }

  protected function get_path() {
    return $this -> info -> path;
  }

  protected function get_type() {
    return $this -> info -> type;
  }

  protected function get_mode() {
    return $this -> info -> mode;
  }

  protected function get_size() {
    return $this -> info -> size;
  }

  protected function get_commit() {
    return $this -> cached(__METHOD__, function() {
      $hash = trim($this -> context -> execute([
        'rev-list', '-1', $this -> ref -> name, '--', $this -> path
      ]));
      return $this -> context -> commit($hash);
    });
  }

  protected function get_ext() {
    $result = pathinfo($this -> info -> path, PATHINFO_EXTENSION);
    return is_string($result) ? $result : '';
  }

  protected function get_data() {
    return $this -> context -> execute([
      'show', $this -> ref -> name . ':' . $this -> path
    ]);
  }

  protected function get_mime() {
    return $this -> cached(__METHOD__, function() {
      $process = $this -> context -> git -> start([
        'show', $this -> ref -> name . ':' . $this -> path
      ]);
      $process -> stdin() -> close();
      $process -> stderr() -> close();

      $process = new Process('file --brief --mime-type -', null, null, [
        StdPipe::STDIN => $process -> stdout(),
      ]);
      $result = strtolower(trim($process -> stdout() -> read()));
      $process -> close();

      if ($result == 'text/plain') {
        if (in_array(strtolower($this -> ext), ['md', 'markdown'])) {
          $result = 'text/markdown';
        }
      }

      return $result;
    });
  }

  protected function get_lines() {
    return $this -> cached(__METHOD__, function() {
      $process = $this -> context -> git -> start([
        'show', $this -> ref -> name . ':' . $this -> path
      ]);

      $totalCount = 0;
      $nonEmptyCount = 0;

      $stdout = $process -> stdout();
      $prev = null;
      while (!$stdout -> eof()) {
        $buffer = $stdout -> read(1024);
        if ($buffer === false) break;
        if ($buffer === '') continue;

        $buffer = explode("\n", $prev . $buffer);
        $prev = array_pop($buffer);
        foreach ($buffer as $line) {
          $line = preg_replace('#\s+#uS', '', $line);
          $totalCount += 1;
          if ($line != '') $nonEmptyCount += 1;
        }
      }
      if ($prev !== null) {
        $totalCount += 1;
      }

      $stderr = $process -> stderr() -> read();
      $exitCode = $process -> close();

      if ($exitCode != 0) throw new Exception($stderr);

      return (object)[
        'total' => $totalCount,
        'nonEmpty' => $nonEmptyCount,
      ];
    });
  }

  public function __construct(RepositoryContext $context, Ref $ref, Tree $parent, $info) {
    $this -> context = $context;
    $this -> _ref = $ref;
    $this -> _parent = $parent;
    $this -> _info = $info;
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

  public function matchesGlob(...$globs) {
    $globs = prepare_string_list($globs, '*');
    foreach ($globs as $glob) {
      if (matches_glob($this -> path, $glob)) {
        return true;
      }
    }
    return false;
  }

  public function raw() {
    return $this -> context -> execute([
      'show',
      $this -> ref -> name . ':' . $this -> path
    ], true);
  }

}
