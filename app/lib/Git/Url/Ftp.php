<?php

namespace Git\Url;

class Ftp implements UrlInterface {

  private function validateUrlComponents(UrlComponents $components): ?UrlComponents {
    if (!in_array($components -> scheme, ['ftp', 'ftps'])) {
      return null;
    }
    return clone $components;
  }

  public function create(UrlComponents $components): ?string {
    $components = $this -> validateUrlComponents($components);
    if ($components) {
      // clear port if default
      if (($components -> scheme == 'ftp') && ($components -> port == 20)) {
        $components -> port = null;
      }
    }
    return $components ? $components -> __toString() : null;
  }

  public function parse(string $url): ?UrlComponents {
    return $this -> validateUrlComponents(new UrlComponents($url));
  }

}
