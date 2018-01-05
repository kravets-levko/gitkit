<?php

namespace Classes;

trait Properties {

  protected $_propertyCache = [];

  protected function cachedClear($name) {
    unset($this -> _propertyCache[$name]);
  }

  protected function cachedUpdate($name, $value) {
    $this -> _propertyCache[$name] = $value;
  }

  public function __isset($name) {
    return method_exists($this, 'get_' . $name) ||
      method_exists($this, 'cached_' . $name) ||
      method_exists($this, 'set_' . $name);
  }

  public function __get($name) {
    if (array_key_exists($name, $this -> _propertyCache)) {
      return $this -> _propertyCache[$name];
    }

    $getter = 'get_' . $name;
    if (method_exists($this, $getter)) {
      return $this -> {$getter}();
    }

    $getter = 'cached_' . $name;
    if (method_exists($this, $getter)) {
      $result = $this -> {$getter}();
      $this -> _propertyCache[$name] = $result;
      return $result;
    }

    $class = get_class($this);
    $setter = 'set_' . $name;
    if (method_exists($this, $setter)) {
      trigger_error("Property ${class}::${name} is write-only.", E_USER_NOTICE);
    } else {
      trigger_error("Property ${class}::${name} does not exist.", E_USER_NOTICE);
    }
    return null;
  }

  public function __set($name, $value) {
    $setter = 'set_' . $name;
    if (method_exists($this, $setter)) {
      $this -> {$setter}();
      return;
    }

    $class = get_class($this);
    if (method_exists($this, 'get_' . $name) || method_exists($this, 'cached_' . $name)) {
      trigger_error("Property ${class}::${name} is read-only.", E_USER_NOTICE);
    } else {
      trigger_error("Property ${class}::${name} does not exist.", E_USER_NOTICE);
    }
  }

}
