<?php

namespace Classes;

trait Properties {

  public function __isset($name) {
    return method_exists($this, 'get_' . $name) ||
      method_exists($this, 'set_' . $name);
  }

  public function __get($name) {
    $getter = 'get_' . $name;
    if (method_exists($this, $getter)) {
      return $this -> {$getter}();
    } else {
      $class = get_class($this);
      $setter = 'set_' . $name;
      if (method_exists($this, $setter)) {
        trigger_error("Property ${class}::${name} is write-only.", E_USER_NOTICE);
      } else {
        trigger_error("Property ${class}::${name} does not exist.", E_USER_NOTICE);
      }
      return null;
    }
  }

  public function __set($name, $value) {
    $setter = 'set_' . $name;
    if (method_exists($this, $setter)) {
      $this -> {$setter}();
    } else {
      $class = get_class($this);
      $getter = 'get_' . $name;
      if (method_exists($this, $getter)) {
        trigger_error("Property ${class}::${name} is read-only.", E_USER_NOTICE);
      } else {
        trigger_error("Property ${class}::${name} does not exist.", E_USER_NOTICE);
      }
    }
  }

}
