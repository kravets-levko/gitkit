<?php

namespace Git\Url;

class UrlComponents {

  public $scheme = null;
  public $user = null;
  public $password = null;
  public $host = null;
  public $port = null;
  public $path = null;

  public function __construct($url = null) {
    if (is_string($url)) {
      $parsed = parse_url($url);
      $scheme = @$parsed['scheme'];
      // if url does not have schema - `parse_url` may return strange results.
      // detect it, add fake schema, parse again and unset schema
      // (original url does not have it)
      if (($scheme === null) || ($scheme === '')) {
        $parsed = parse_url('fake://' . $url);
        unset($parsed['scheme']);
      }
      $url = $parsed;
    }
    if (is_array($url)) {
      $this -> scheme = @$url['scheme'];
      $this -> user = @$url['user'];
      $this -> password = @$url['pass'];
      $this -> host = @$url['host'];
      $this -> port = @$url['port'];
      $this -> path = @$url['path'];
    }
  }

  public function __toString(): string {
    $result = [];
    if (is_string($this -> scheme) && ($this -> scheme != '')) {
      $result[] = $this -> scheme . '://';
    }
    if (is_string($this -> user) && ($this -> user != '')) {
      $result[] = $this -> user;
      if (is_string($this -> password) && ($this -> password != '')) {
        $result[] = ':' . $this -> password;
      }
      $result[] = '@';
    }
    if (is_string($this -> host) && ($this -> host) != '') {
      $result[] = $this -> host;
      if (is_int($this -> port) && ($this -> port > 0)) {
        $result[] = ':' . $this -> port;
      }
    }
    if (is_string($this -> path) && ($this -> path != '')) {
      $result[] = $this -> path;
    }
    return implode('', $result);
  }

}
