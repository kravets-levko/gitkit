<?php

namespace Classes\Twig\NodeVisitor;

use Twig_BaseNodeVisitor as TwigBaseNodeVisitor;
use Twig_Node as TwigNode;
use Twig_Environment as TwigEnvironment;

function resolve_path(string $relative, string $base): string {
  if (substr($relative, 0, 1) !== '/') {
    $relative = explode('/', $relative);
    $result = explode('/', $base);
    foreach ($relative as $name) {
      if ($name === '.') continue;
      if (($name === '..') && (count($result) > 0)) {
        array_pop($result);
        continue;
      }
      $result[] = $name;
    }
    return implode('/', $result);
  } else {
    return substr($relative, 1);
  }
}

/**
 * Makes all paths relative to current file (if possible).
 */
final class RelativePath extends TwigBaseNodeVisitor {

  protected function doEnterNode(TwigNode $node, TwigEnvironment $env) {
    $baseName = $node -> getTemplateName();
    if (is_string($baseName)) {
      // {% extends %}
      if ($node -> hasNode('parent')) {
        $parent = $node -> getNode('parent');
        if ($parent -> hasAttribute('value')) {
          $relative = $parent -> getAttribute('value');
          if (is_string($relative)) {
            $resolved = resolve_path($relative, pathinfo($baseName, PATHINFO_DIRNAME));
            $parent -> setAttribute('value', $resolved);
          }
        }
      }

      // {% include %} {% import %}
      if (
        in_array($node -> getNodeTag(), ['include', 'import']) &&
        $node -> hasNode('expr')
      ) {
        $expr = $node -> getNode('expr');
        if ($expr -> hasAttribute('value')) {
          $relative = $expr -> getAttribute('value');
          if (is_string($relative)) {
            $resolved = resolve_path($relative, pathinfo($baseName, PATHINFO_DIRNAME));
            $expr -> setAttribute('value', $resolved);
          }
        }
      }
    }

    return $node;
  }

  protected function doLeaveNode(TwigNode $node, TwigEnvironment $env) {
    return $node;
  }

  public function getPriority() {
    return 0;
  }

}
