<?php

namespace System;

function __is_ws($char) {
  return (bool)preg_match('#\s#U', $char);
}

function __skip_ws($file) {
  $result = false;
  while (!feof($file)) {
    $result = fread($file, 1);
    if (($result === false) || ($result === '')) break;
    if (!__is_ws($result)) break;
  }
  return is_string($result) && ($result !== '') ? $result : false;
}

function __read_comment($file) {
  $result = '';
  while (!feof($file)) {
    $char = fread($file, 1);
    if (($char === false) || ($char === '') || ($char === "\n")) break;
    $result .= $char;
  }
  return $result;
}

function __read_escape($file) {
  if (!feof($file)) {
    $char = fread($file, 1);
    if (($char === false) || ($char === '')) return '\\';

    if (strpos('0123456789', $char) !== false) {
      $code = $char;
      $char = '';
      while (!feof($file)) {
        $char = fread($file, 1);
        if (($char === false) || ($char === '')) break;
        if (strpos('0123456789', $char) === false) break;
        if ((int)($code . $char) >= 255) break;

        $code .= $char;
        $char = '';
      }
      return chr((int)$code) . $char;
    } else {
      switch ($char) {
        case '\\': return '\\';
        case 'n': return "\n";
        case 'r': return "\r";
        case 't': return "\t";
        case '"': return '"';
        case "'": return "'";
      }
    }
  }
  return '\\';
}

function __read_key($file, $result = '') {
  while (!feof($file)) {
    $char = fread($file, 1);
    // unexpected end of data
    if (($char === false) || ($char === '')) return false;
    // there may be whitespaces between key and '='
    if (__is_ws($char)) {
      $char = __skip_ws($file);
      if ($char !== '=') return false; // malformed data
    }
    // end of key
    if ($char === '=') break;
    $result .= $char;
  }

  // key cannot be empty
  return $result !== '' ? $result : false;
}

function __read_value($file, $delimiter = null) {
  if (is_string($delimiter) && ($delimiter !== '')) {
    $delimiter = substr($delimiter, 0, 1); // use only first character
  }

  $result = '';
  while (!feof($file)) {
    $char = fread($file, 1);
    // value may be terminated by end of data
    if (($char === false) || ($char === '')) break;

    if ($char === '\\') {
      // escape sequence
      $char = __read_escape($file);
    } else {
      // check for value end
      if (is_string($delimiter) && ($delimiter !== '')) {
        if ($char === $delimiter) break;
      } else {
        if (__is_ws($char)) break;
      }
    }

    $result .= $char;
  }
  return $result !== '' ? $result : null;
}

function __escape_value($value) {
  $result = '';
  $value = strval($value);
  $hasWhitespaces = false;
  $hasDoubleQuote = false;
  $hasSingleQuote = false;

  $literalChars = 'abcdefghijklmnopqrstuvwxyz0123456789' .
    '`\'"-=!@#$%^&*()_+[]{};:,./?|\\';

  for($i = 0; $i < strlen($value); $i++) {
    $char = $value{$i};
    if (__is_ws($char)) $hasWhitespaces = true;
    if ($char == '"') $hasDoubleQuote = true;
    if ($char == "'") $hasSingleQuote = true;

    if (stripos($literalChars, $char) === false) {
      switch ($char) {
        case '\\': $char = '\\\\'; break;
        case "\n": $char = '\\n'; break;
        case "\r": $char = '\\r'; break;
        case "\t": $char = '\\t'; break;
        default: $char = '\\' . ord($char); break;
      }
    }
    $result .= $char;
  }

  if ($hasWhitespaces) {
    if ($hasDoubleQuote) {
      if ($hasSingleQuote) {
        $result = '"' . str_replace('"', '\\"', $result) . '"';
      } else {
        $result = "'" . $result . "'";
      }
    } else {
      $result = '"' . $result . '"';
    }
  }

  return $result;
}

function parse_env_file($file) {
  if (is_string($file)) {
    $handle = fopen($file, 'rb');
    try {
      return parse_env_file($handle);
    } finally {
      fclose($handle);
    }
  } elseif (is_resource($file)) {
    $result = [];
    while (!feof($file)) {
      $char = __skip_ws($file);
      if ($char === false) break;

      // comment
      if ($char == '#') {
        $comment = __read_comment($file);
        $result[] = ['comment', $comment];
        // continue to next iteration
        continue;
      }

      // key=value
      if ($char !== '=') {
        $key = __read_key($file, $char);
        if ($key === false) break; // malformed data / unexpected end of data
        $char = __skip_ws($file);
        if ($char !== false) {
          if (($char === '"') || ($char === "'")) {
            $value = __read_value($file, $char); // read until delimiter
          } else {
            $value = $char . __read_value($file); // read until first whitespace
          }
        } else {
          $value = null;
        }

        $result[] = ['var', $key, $value];
        continue;
      }
    }
    return $result;
  } else {
    return [];
  }
}

function parse_env_str($str) {
  $handle = fopen('php://memory', 'r+');
  try {
    fwrite($handle, @strval($str));
    rewind($handle);
    return parse_env_file($handle);
  } finally {
    fclose($handle);
  }
}

function dump_env_file($file, $data) {
  if (!is_iterable($data)) return;
  if (is_string($file)) {
    $handle = fopen($file, 'wb');
    try {
      dump_env_file($handle, $data);
    } finally {
      fclose($handle);
    }
  } elseif (is_resource($file)) {
    foreach ($data as $datum) {
      if (count($datum) == 0) continue;
      $type = $datum[0];
      switch ($type) {
        case 'comment':
          fwrite($file, '#' . @$datum[1] . "\n");
          break;
        case 'var':
          fwrite($file, @$datum[1] . '=' . __escape_value(@$datum[2]) . "\n");
          break;
      }
    }
  }
}

function dump_env_str($data) {
  if (!is_iterable($data)) return '';
  $handle = fopen('php://memory', 'r+');
  try {
    dump_env_file($handle, $data);
    rewind($handle);
    $result = [];
    while (!feof($handle)) {
      $buffer = fread($handle, 1024);
      if (($buffer === false) || ($buffer === '')) break;
      $result[] = $buffer;
    }
    return implode('', $result);
  } finally {
    fclose($handle);
  }
}

class EnvFile {

  protected $filename;
  public $data = [];

  public function __construct($filename) {
    $this -> filename = $filename;
    $this -> load();
  }

  public function load() {
    $this -> data = [];
    if ($this -> filename) {
      $this -> data = parse_env_file($this -> filename);
    }
    if (!is_array($this -> data)) $this -> data = [];
  }

  public function save() {
    if ($this -> filename) {
      dump_env_file($this -> filename, $this -> data);
    }
  }

  public function variables() {
    $result = [];
    if (is_array($this -> data)) {
      foreach ($this -> data as $datum) {
        @list($type, $key, $value) = $datum;
        if ($type === 'var') $result[$key] = $value;
      }
    }
    return $result;
  }

  public function get($key) {
    if (is_array($this -> data)) {
      foreach ($this -> data as $datum) {
        if ((count($datum) >= 3) && ($datum[0] == 'var') && ($datum[1] == $key)) {
          return $datum[2];
        }
      }
    }
    return null;
  }

  public function set($key, $value, $addIfNotExists = false) {
    $found = false;
    if (is_array($this -> data)) {
      foreach ($this -> data as &$datum) {
        if ((count($datum) >= 3) && ($datum[0] == 'var') && ($datum[1] == $key)) {
          $datum = ['var', $key, $value];
          $found = true;
        }
      }
      unset($datum);
    }
    if ($addIfNotExists && !$found) {
      if (is_array($this -> data)) $this -> data = [];
      $this -> data[] = ['var', $key, $value];
    }
  }

  static public function parseEnvFile($file) {
    return parse_env_file($file);
  }

  static public function parseEnvStr($str) {
    return parse_env_str($str);
  }

}
