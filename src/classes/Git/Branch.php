<?php

namespace Classes\Git;

class Branch {

  private $repository;
  private $name;

  public function __construct(Repository $repository, string $name) {
    $this -> repository = $repository;
    $this -> name = $name;
  }

  public function getRepository() {
    return $this -> repository;
  }

  public function getName() {
    return $this -> name;
  }

}
