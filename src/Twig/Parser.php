<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Twig;

use Maba\Bundle\TwigTemplateModificationBundle\NodeAttributes;
use Twig_Error_Syntax as SyntaxError;
use Twig_ExpressionParser as ExpressionParser;
use Twig_Node as Node;
use Twig_Node_Print as PrintNode;
use Twig_Node_Text as TextNode;
use Twig_NodeInterface as NodeInterface;
use Twig_Parser as BaseParser;
use Twig_Token as Token;
use Twig_TokenParserBroker as TokenParserBroker;
use Twig_TokenParserInterface as TokenParserInterface;
use Twig_TokenStream as TokenStream;

/**
 * @property TokenStream $stream
 * @property ExpressionParser $expressionParser
 * @property TokenParserBroker $handlers
 */
class Parser extends BaseParser
{
    /**
     * @param ExpressionParser $expressionParser
     */
    public function setExpressionParser(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    public function subparse($test, $dropNeedle = false)
    {
        $initialToken = $this->getCurrentToken();
        $lineno = $initialToken->getLine();
        $rv = array();
        while (!$this->stream->isEOF()) {
            $startToken = $this->getCurrentToken();
            switch ($startToken->getType()) {
                case Token::TEXT_TYPE:
                    $token = $this->stream->next();

                    try {
                        $endToken = $this->stream->look();
                    } catch (SyntaxError $exception) {
                        $endToken = null;
                    }

                    $rv[] = $this->relateNodeWithToken(
                        new TextNode($token->getValue(), $token->getLine()),
                        $startToken,
                        $endToken
                    );
                    break;

                case Token::VAR_START_TYPE:
                    $token = $this->stream->next();
                    $expr = $this->expressionParser->parseExpression();
                    $this->stream->expect(Token::VAR_END_TYPE);

                    $rv[] = $this->relateNodeWithToken(
                        new PrintNode($expr, $token->getLine()),
                        $startToken,
                        $this->stream->getCurrent()
                    );
                    break;

                case Token::BLOCK_START_TYPE:
                    $this->stream->next();
                    $token = $this->getCurrentToken();

                    if ($token->getType() !== Token::NAME_TYPE) {
                        throw new SyntaxError(
                            'A block must start with a tag name.',
                            $token->getLine(),
                            $this->getFilename()
                        );
                    }

                    if (null !== $test && call_user_func($test, $token)) {
                        $endToken = $this->stream->look(-1);

                        if ($dropNeedle) {
                            $this->stream->next();
                        }

                        if (1 === count($rv)) {
                            return $rv[0];
                        }

                        return $this->relateNodeWithToken(new Node($rv, array(), $lineno), $initialToken, $endToken);
                    }

                    $subparser = $this->handlers->getTokenParser($token->getValue());
                    if (null === $subparser) {
                        if (null !== $test) {
                            $e = new SyntaxError(
                                sprintf('Unexpected "%s" tag', $token->getValue()),
                                $token->getLine(),
                                $this->getFilename()
                            );

                            if (is_array($test) && isset($test[0]) && $test[0] instanceof TokenParserInterface) {
                                $e->appendMessage(
                                    sprintf(
                                        ' (expecting closing tag for the "%s" tag defined near line %s).',
                                        $test[0]->getTag(),
                                        $lineno
                                    )
                                );
                            }
                        } else {
                            $e = new SyntaxError(
                                sprintf('Unknown "%s" tag.', $token->getValue()),
                                $token->getLine(),
                                $this->getFilename()
                            );
                            $e->addSuggestions($token->getValue(), array_keys($this->env->getTags()));
                        }

                        throw $e;
                    }

                    $this->stream->next();

                    $node = $subparser->parse($token);
                    if (null !== $node) {
                        $rv[] = $this->relateNodeWithToken($node, $startToken, $this->stream->getCurrent());
                    }
                    break;

                default:
                    throw new SyntaxError('Lexer or parser ended up in unsupported state.', 0, $this->getFilename());
            }
        }

        if (1 === count($rv)) {
            return $rv[0];
        }

        return $this->relateNodeWithToken(new Node($rv, array(), $lineno), $initialToken, null);
    }

    private function relateNodeWithToken(NodeInterface $node, Token $startToken, Token $nextToken = null)
    {
        if (!$startToken instanceof ExtendedToken || $nextToken !== null && !$nextToken instanceof ExtendedToken) {
            throw new \RuntimeException('Token is not instance of ExtendedToken');
        }
        if (!$node instanceof Node) {
            throw new \RuntimeException('Node is not instance of Node - NodeInterface is deprecated and not supported');
        }

        $node->setAttribute(NodeAttributes::ATTRIBUTE_START_TOKEN, $startToken);
        $node->setAttribute(NodeAttributes::ATTRIBUTE_NEXT_TOKEN, $nextToken);

        return $node;
    }
}
