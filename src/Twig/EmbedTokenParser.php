<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Twig;

use Twig_Token;
use Twig_TokenParser_Embed as BaseEmbedTokenParser;

/**
 * Used to replace Twig_Token with ExtendedToken - all other code is not modified
 */
class EmbedTokenParser extends BaseEmbedTokenParser
{
    public function parse(Twig_Token $token)
    {
        $stream = $this->parser->getStream();

        $parent = $this->parser->getExpressionParser()->parseExpression();

        list($variables, $only, $ignoreMissing) = $this->parseArguments();

        // inject a fake parent to make the parent() function work
        $stream->injectTokens(array(
            new ExtendedToken(Twig_Token::BLOCK_START_TYPE, '', $token->getLine()),
            new ExtendedToken(Twig_Token::NAME_TYPE, 'extends', $token->getLine()),
            new ExtendedToken(Twig_Token::STRING_TYPE, '__parent__', $token->getLine()),
            new ExtendedToken(Twig_Token::BLOCK_END_TYPE, '', $token->getLine()),
        ));

        $module = $this->parser->parse($stream, array($this, 'decideBlockEnd'), true);

        // override the parent with the correct one
        $module->setNode('parent', $parent);

        $this->parser->embedTemplate($module);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new \Twig_Node_Embed($module->getAttribute('filename'), $module->getAttribute('index'), $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
    }
}
