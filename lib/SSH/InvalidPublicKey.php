<?php

namespace SSH;

use Throwable;

class InvalidPublicKey extends Exception {

  public function __construct(string $message = '', int $code = 0, Throwable $previous = null) {
    parent ::__construct('Invalid SSH public key', $code, $previous);
  }

}
