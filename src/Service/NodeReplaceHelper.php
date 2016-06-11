<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Service;

use Maba\Bundle\TwigTemplateModificationBundle\NodeAttributes;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\ExtendedToken;
use Twig_Node as Node;

class NodeReplaceHelper
{

    public function getStartPosition(Node $node)
    {
        return $this->getToken($node, NodeAttributes::ATTRIBUTE_START_TOKEN)->getStartCursor();
    }

    public function getEndPosition(Node $node)
    {
        $endToken = $this->getToken($node, NodeAttributes::ATTRIBUTE_NEXT_TOKEN);

        if ($endToken === null) {
            return strlen($this->getRootSource($node));
        }

        return $endToken->getStartCursor();
    }

    public function getLength(Node $node)
    {
        return $this->getEndPosition($node) - $this->getStartPosition($node);
    }

    public function getSource(Node $node)
    {
        return substr($this->getRootSource($node), $this->getStartPosition($node), $this->getLength($node));
    }

    /**
     * @param Node $contextNode
     * @param Node $removedNode
     * @param string $replacement
     * @return string
     */
    public function getReplacedSource(Node $contextNode, Node $removedNode, $replacement)
    {
        $contextStart = $this->getStartPosition($contextNode);
        $removalStart = $this->getStartPosition($removedNode) - $contextStart;

        return substr_replace(
            $this->getSource($contextNode),
            $replacement,
            $removalStart,
            $this->getLength($removedNode)
        );
    }

    protected function getRootSource(Node $node)
    {
        if ($node->hasAttribute(NodeAttributes::ATTRIBUTE_SOURCE)) {
            return $node->getAttribute(NodeAttributes::ATTRIBUTE_SOURCE);
        }
        /** @var Node $rootNode */
        $rootNode = $node->getAttribute(NodeAttributes::ATTRIBUTE_ROOT_NODE);
        return $rootNode->getAttribute(NodeAttributes::ATTRIBUTE_SOURCE);
    }

    /**
     * @param Node $node
     * @param string $attribute
     * @return ExtendedToken
     */
    protected function getToken(Node $node, $attribute)
    {
        if (!$node->hasAttribute($attribute)) {
            $token = $this->findChildToken($node, $attribute);
            if ($token === null) {
                $token = $this->findParentToken($node, $attribute);
            }
            if ($token === null) {
                throw new \RuntimeException(
                    sprintf('Cannot find token related to node %s (line %s)', $node->getNodeTag(), $node->getLine())
                );
            }
            return $token;
        }
        return $node->getAttribute($attribute);
    }

    protected function findChildToken(Node $node, $attribute)
    {
        /** @var Node $childNode */
        foreach ($node as $childNode) {
            if ($childNode !== null) {
                if ($childNode->hasAttribute($attribute)) {
                    return $childNode->getAttribute($attribute);
                }

                $token = $this->findChildToken($childNode, $attribute);
                if ($token !== null) {
                    return $token;
                }
            }
        }

        return null;
    }

    protected function findParentToken(Node $node, $attribute)
    {
        $parentNode = $node->getAttribute(NodeAttributes::ATTRIBUTE_PARENT_NODE);
        if ($parentNode !== null) {
            if ($parentNode->hasAttribute($attribute)) {
                return $parentNode->getAttribute($attribute);
            }

            return $this->findParentToken($parentNode, $attribute);
        }

        return null;
    }
}
