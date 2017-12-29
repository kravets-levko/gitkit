<?php

namespace Classes\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Functions extends AbstractExtension {

  public function getFilters() {
    return [
      new TwigFilter('md5', [$this, 'md5']),
      new TwigFilter('format_bytes', [$this, 'formatBytes']),
    ];
  }

  public function md5($value) {
    return md5($value);
  }

  public function formatBytes($value) {
    return format_bytes($value);
  }

}
