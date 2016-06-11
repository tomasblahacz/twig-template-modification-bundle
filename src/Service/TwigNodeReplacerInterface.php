<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Service;

use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use Twig_Node as Node;

interface TwigNodeReplacerInterface
{

    /**
     * @param Node $node
     * @param TemplateContext $context
     *
     * @return null|string string if this node should be replaced with given twig code
     */
    public function replace(Node $node, TemplateContext $context);
}
