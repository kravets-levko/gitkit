<?php

namespace Classes\Twig\Node;

use Twig_Node as TwigNode;
use Twig_Compiler as TwigCompiler;

/**
 * Represents a pre node.
 *
 * It trims each line and removes empty lines at beginning and end
 */
class Pre extends TwigNode {
  public function __construct(TwigNode $body, $lineno, $tag = 'pre') {
    parent::__construct(['body' => $body], [], $lineno, $tag);
  }

  public function compile(TwigCompiler $compiler) {
    $compiler
      -> addDebugInfo($this)
      -> write('ob_start();' . PHP_EOL)
      -> subcompile($this -> getNode('body'))
      -> write('echo implode("\n", array_map("trim", explode("\n", trim(ob_get_clean()))));' . PHP_EOL)
    ;
  }
}
