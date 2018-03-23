<?php

namespace Classes\Twig\TokenParser;

use Twig_TokenParser as TwigTokenParser;
use Twig_Token as TwigToken;

use Classes\Twig\Node\Pre as TwigNodePre;

/**
 * Trim each line; remove empty lines at beginning and end.
 *
 * {% pre %}
 *
 *    test
 *    123
 *
 * {% endpre %}
 *
 * outputs: "test\n123"
 */
final class Pre extends TwigTokenParser {

  public function parse(TwigToken $token) {
    $lineno = $token -> getLine();

    $this -> parser -> getStream() -> expect(TwigToken::BLOCK_END_TYPE);
    $body = $this -> parser -> subparse(array($this, 'decideSpacelessEnd'), true);
    $this -> parser -> getStream() -> expect(TwigToken::BLOCK_END_TYPE);

    return new TwigNodePre($body, $lineno, $this -> getTag());
  }

  public function decideSpacelessEnd(TwigToken $token) {
    return $token -> test('endpre');
  }

  public function getTag() {
    return 'pre';
  }
}
