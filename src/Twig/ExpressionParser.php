<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Twig;

use Maba\Bundle\TwigTemplateModificationBundle\NodeAttributes;
use Twig_ExpressionParser as BaseExpressionParser;
use Twig_Node_Expression as ExpressionNode;

class ExpressionParser extends BaseExpressionParser
{
    /**
     * @param int $precedence
     * @return ExpressionNode
     */
    public function parseExpression($precedence = 0)
    {
        $startToken = $this->parser->getCurrentToken();
        $expression = parent::parseExpression($precedence);
        $expression->setAttribute(NodeAttributes::ATTRIBUTE_START_TOKEN, $startToken);
        $expression->setAttribute(NodeAttributes::ATTRIBUTE_NEXT_TOKEN, $this->parser->getCurrentToken());
        return $expression;
    }
}
