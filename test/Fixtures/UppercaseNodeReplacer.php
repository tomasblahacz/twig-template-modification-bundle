<?php

namespace Maba\Tests\Fixtures;

use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use Maba\Bundle\TwigTemplateModificationBundle\Service\TwigNodeReplacerInterface;
use Twig_Node as Node;

class UppercaseNodeReplacer implements TwigNodeReplacerInterface
{
    public function replace(Node $node, TemplateContext $context)
    {
        if (!$node instanceof \Twig_Node_Expression_Filter) {
            return null;
        }

        $filterName = $node->getNode('filter')->getAttribute('value');
        if ($filterName !== 'upper') {
            return null;
        }

        $innerNode = $node->getNode('node');
        if (!$innerNode instanceof \Twig_Node_Expression_Constant) {
            return null;
        }

        $value = $innerNode->getAttribute('value');

        if (!is_string($value)) {
            return null;
        }

        return var_export(strtoupper($value), true);
    }
}
