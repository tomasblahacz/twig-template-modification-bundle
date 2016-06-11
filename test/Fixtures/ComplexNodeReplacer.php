<?php

namespace Maba\Tests\Fixtures;

use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use Maba\Bundle\TwigTemplateModificationBundle\Service\NodeReplaceHelper;
use Maba\Bundle\TwigTemplateModificationBundle\Service\TwigNodeReplacerInterface;
use Twig_Node as Node;

class ComplexNodeReplacer implements TwigNodeReplacerInterface
{

    protected $nodeReplaceHelper;

    public function __construct(NodeReplaceHelper $nodeReplaceHelper)
    {
        $this->nodeReplaceHelper = $nodeReplaceHelper;
    }

    public function replace(Node $node, TemplateContext $context)
    {
        if (!$node instanceof \Twig_Node_Block) {
            return null;
        }

        if ($node->getAttribute('name') !== 'javascripts') {
            return null;
        }

        $body = $node->getNode('body');

        return $this->replaceRecursivelly($body, $body);
    }

    protected function replaceRecursivelly(Node $parent, Node $body)
    {
        foreach ($parent as $index => $node) {
            if ($node instanceof \Twig_Node_Expression_Constant && $node->getAttribute('value') === 'jquery.js') {
                return $this->nodeReplaceHelper->getReplacedSource($body, $node, 'random()');
            } else {
                $replaced = $this->replaceRecursivelly($node, $body);
                if ($replaced !== null) {
                    return $replaced;
                }
            }
        }

        return null;
    }
}
