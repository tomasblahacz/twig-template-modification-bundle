<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Twig;

use Twig_Lexer as BaseLexer;
use Twig_Token as Token;

class Lexer extends BaseLexer
{
    /**
     * @var ExtendedToken|null
     */
    private $lastToken = null;

    protected function pushToken($type, $value = '')
    {
        // do not push empty text tokens
        if (Token::TEXT_TYPE === $type && '' === $value) {
            return;
        }

        $cursor = $this->cursor;
        if ($type === Token::BLOCK_START_TYPE || $type === Token::VAR_START_TYPE) {
            $cursor -= 2;
        }

        if ($this->lastToken !== null) {
            $this->lastToken->setEndCursor($cursor);
        }

        $token = new ExtendedToken($type, $value, $this->lineno);
        $token->setStartCursor($cursor);
        if ($type === Token::EOF_TYPE) {
            $token->setEndCursor($cursor);
        }

        $this->lastToken = $token;

        $this->tokens[] = $token;
    }
}
