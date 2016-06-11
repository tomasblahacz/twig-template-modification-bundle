<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Factory;

use Maba\Bundle\TwigTemplateModificationBundle\Twig\EmbedTokenParser;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\ExpressionParser;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\Lexer;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\Parser;
use Twig_Environment as Environment;
use Twig_TokenParserBroker as TokenParserBroker;

class EnvironmentFactory
{
    private $environment;
    private $lexerOptions;

    public function __construct(Environment $environment, array $lexerOptions = array())
    {
        $this->environment = $environment;
        $this->lexerOptions = $lexerOptions;
    }

    public function createEnvironment()
    {
        $environment = clone $this->environment;

        // replace base token parser with extended one
        /** @var TokenParserBroker $tokenParserBroker */
        $tokenParserBroker = $environment->getTokenParsers();
        $tokenParserBroker->addTokenParser(new EmbedTokenParser());

        $parser = new Parser($environment);
        $lexer = new Lexer($environment, $this->lexerOptions);

        $expressionParser = new ExpressionParser(
            $parser,
            $environment->getUnaryOperators(),
            $environment->getBinaryOperators()
        );
        $parser->setExpressionParser($expressionParser);

        $environment->setParser($parser);
        $environment->setLexer($lexer);

        return $environment;
    }
}
