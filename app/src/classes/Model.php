<?php

namespace Classes;

use Respect\Validation\Validator;
use Respect\Validation\Exceptions\{ ValidationException, NestedValidationException };
use Utils\Mixin\{ Properties, Cached };

class Model {

  use Properties;
  use Cached;

  protected $_validated = false;
  protected $_attributes = [];
  protected $_errors = [];

  protected function getValidators($v) {
    return [];
  }

  protected function get_validators() {
    return $this -> cached('validators', function() {
      return array_filter($this -> getValidators(Validator::class), function($v) {
        return $v instanceof Validator;
      });
    });
  }

  protected function get_safeAttributeNames() {
    return array_keys($this -> validators);
  }

  protected function get_attributes() {
    return $this -> _attributes;
  }

  protected function set_attributes($attributes) {
    if (is_array($attributes) && (count($attributes) > 0)) {
      $this -> _attributes = array_intersect_key($attributes, $this -> validators);
    } else {
      $this -> _attributes = [];
    }
  }

  protected function get_errors() {
    return $this -> errors();
  }

  protected function get_validated() {
    return $this -> _validated;
  }

  public function __construct($attributes = null) {
    $this -> attributes = $attributes;
    $this -> _validated = false;
    $this -> _errors = [];
  }

  public function get($attribute) {
    return array_key_exists($attribute, $this -> _attributes)
      ? $this -> _attributes[$attribute]
      : null;
  }

  public function set($attribute, $value) {
    $validators = $this -> validators;
    if (array_key_exists($attribute, $validators)) {
      $this -> _attributes[$attribute] = $value;
    }
  }

  public function validate() {
    if (!$this -> _validated) {
      $this -> _errors = [];

      $validators = $this -> validators;
      foreach ($this -> _attributes as $name => $value) {
        if (array_key_exists($name, $validators)) {
          $v = $validators[$name];
          try {
            $v -> assert($value);
          } catch(NestedValidationException $exception) {
            // Remove first message as it is duplicated
            $this -> _errors[$name] = array_slice($exception -> getMessages(), 1);
          } catch (ValidationException $exception) {
            $this -> _errors[$name] = [$exception -> getMessage()];
          }
        } else {
          $this -> _errors[$name] = ["Attribute '${name}' has no validators defined."];
        }
      }
      $this -> _validated = true;
    }
    return count($this -> _errors) == 0;
  }

  public function valid($attribute = null) {
    $this -> validate();
    return count($this -> errors($attribute)) == 0;
  }

  public function invalid($attribute = null) {
    $this -> validate();
    return count($this -> errors($attribute)) > 0;
  }

  public function reset() {
    $this -> _validated = false;
    $this -> _errors = [];
    $this -> cachedUnset('validators');
  }

  public function errors($attribute = null) {
    if ($attribute === null) {
      return $this -> _errors;
    } elseif (array_key_exists($attribute, $this -> _errors)) {
      return $this -> _errors[$attribute];
    }
    return [];
  }

}
