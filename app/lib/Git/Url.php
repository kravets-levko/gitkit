<?php

namespace Git;

use Git\Url\{ UrlInterface, Http, Git, SSH, Ftp };

class Url {

  static private $http = null;
  static private $ssh = null;
  static private $git = null;
  static private $ftp = null;

  static public function http(): UrlInterface {
    if (self::$http === null) self::$http = new Http();
    return self::$http;
  }

  static public function ssh(): UrlInterface {
    if (self::$ssh === null) self::$ssh = new SSH();
    return self::$ssh;
  }

  static public function git(): UrlInterface {
    if (self::$git === null) self::$git = new Git();
    return self::$git;
  }

  static public function ftp(): UrlInterface {
    if (self::$ftp === null) self::$ftp = new Ftp();
    return self::$ftp;
  }

}
