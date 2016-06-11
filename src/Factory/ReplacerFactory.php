<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Factory;

use Maba\Bundle\TwigTemplateModificationBundle\Service\FilesReplacer;
use Maba\Bundle\TwigTemplateModificationBundle\Service\NodeReplaceHelper;
use Maba\Bundle\TwigTemplateModificationBundle\Service\TemplateReplacer;
use Maba\Bundle\TwigTemplateModificationBundle\Service\TwigNodeReplacerInterface;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\Parser;
use Twig_Lexer as Lexer;
use Twig_LoaderInterface as LoaderInterface;

class ReplacerFactory
{
    private $loader;
    private $lexer;
    private $parser;
    private $nodeReplaceHelper;
    private $directoryPatterns;

    public function __construct(
        LoaderInterface $loader,
        Lexer $lexer,
        Parser $parser,
        NodeReplaceHelper $nodeReplaceHelper,
        array $directoryPatterns
    ) {
        $this->loader = $loader;
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->nodeReplaceHelper = $nodeReplaceHelper;
        $this->directoryPatterns = $directoryPatterns;
    }

    /**
     * @param array $directoryPatterns
     */
    public function setDirectoryPatterns(array $directoryPatterns)
    {
        $this->directoryPatterns = $directoryPatterns;
    }

    /**
     * @param string $directoryPattern
     */
    public function addDirectoryPattern($directoryPattern)
    {
        $this->directoryPatterns[] = $directoryPattern;
    }

    /**
     * @param TwigNodeReplacerInterface[] $nodeReplacers
     * @return TemplateReplacer
     */
    public function createTemplateReplacer(array $nodeReplacers)
    {
        return new TemplateReplacer(
            $this->loader,
            $this->lexer,
            $this->parser,
            $this->nodeReplaceHelper,
            $nodeReplacers
        );
    }

    /**
     * @param TwigNodeReplacerInterface[] $nodeReplacers
     * @return FilesReplacer
     */
    public function createFilesReplacer(array $nodeReplacers)
    {
        return new FilesReplacer(
            $this->createTemplateReplacer($nodeReplacers),
            $this->directoryPatterns
        );
    }
}
