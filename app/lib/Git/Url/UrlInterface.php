<?php

namespace Git\Url;

interface UrlInterface {

  public function create(UrlComponents $components): ?string;

  public function parse(string $url): ?UrlComponents;

}
