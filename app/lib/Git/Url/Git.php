<?php

namespace Git\Url;

class Git implements UrlInterface {

  private function validateUrlComponents(UrlComponents $components): ?UrlComponents {
    if (!in_array($components -> scheme, ['git'])) {
      return null;
    }
    return clone $components;
  }

  public function create(UrlComponents $components): string {
    $components = $this -> validateUrlComponents($components);
    return $components ? $components -> __toString() : null;
  }

  public function parse(string $url): UrlComponents {
    return $this -> validateUrlComponents(new UrlComponents($url));
  }

}
