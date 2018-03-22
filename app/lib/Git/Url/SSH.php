<?php

namespace Git\Url;

class SSH implements UrlInterface {

  private function validateShortFormatComponents(UrlComponents $components): bool {
    return (
      in_array($components -> port, [22, '22', '', null], true) &&
      (is_string($components -> user) && ($components -> user != '')) &&
      (is_string($components -> host) && ($components -> host != '')) &&
      (is_string($components -> path) && ($components -> path != ''))
    );
  }

  private function validateUrlComponents(UrlComponents $components): ?UrlComponents {
    if (in_array($components -> scheme, [null, ''], true)) {
      if ($this -> validateShortFormatComponents($components)) {
        $components -> scheme = 'ssh';
      } else {
        return null;
      }
    } elseif (!in_array($components -> scheme, ['ssh'])) {
      return null;
    }
    return clone $components;
  }

  public function create(UrlComponents $components): ?string {
    $components = $this -> validateUrlComponents($components);

    if ($components) {
      if ($this -> validateShortFormatComponents($components)) {
        $path = $components -> path;
        if (substr($path, 0, 3) == '/~/') $path = substr($path, 3);
        return $components -> user . '@' . $components -> host . ':' . $path;
      }

      return $components -> __toString();
    }

    return null;
  }

  public function parse(string $url): ?UrlComponents {
    return $this -> validateUrlComponents(new UrlComponents($url));
  }

}
