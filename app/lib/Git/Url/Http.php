<?php

namespace Git\Url;

class Http implements UrlInterface {

  private function validateUrlComponents(UrlComponents $components): ?UrlComponents {
    if (!in_array($components -> scheme, ['http', 'https'])) {
      return null;
    }
    return clone $components;
  }

  public function create(UrlComponents $components): ?string {
    $components = $this -> validateUrlComponents($components);
    if ($components) {
      // clear port if default
      if (($components -> scheme == 'http') && ($components -> port == 80)) {
        $components -> port = null;
      }
      if (($components -> scheme == 'https') && ($components -> port == 443)) {
        $components -> port = null;
      }
    }

    return $components ? $components -> __toString() : null;
  }

  public function parse(string $url): UrlComponents {
    return $this -> validateUrlComponents(new UrlComponents($url));
  }

}
