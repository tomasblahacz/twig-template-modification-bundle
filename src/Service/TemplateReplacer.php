<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Service;

use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use Maba\Bundle\TwigTemplateModificationBundle\NodeAttributes;
use Maba\Bundle\TwigTemplateModificationBundle\Twig\Parser;
use SplStack;
use Twig_Lexer as Lexer;
use Twig_LoaderInterface as LoaderInterface;
use Twig_Node as Node;

class TemplateReplacer
{
    protected $loader;
    protected $lexer;
    protected $parser;
    private $nodeReplaceHelper;

    /**
     * @var TwigNodeReplacerInterface[]
     */
    protected $replacers = array();

    public function __construct(
        LoaderInterface $loader,
        Lexer $lexer,
        Parser $parser,
        NodeReplaceHelper $nodeReplaceHelper,
        array $nodeReplacers
    ) {
        $this->loader = $loader;
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->nodeReplaceHelper = $nodeReplaceHelper;
        $this->replacers = $nodeReplacers;
    }

    /**
     * @param TemplateContext $context
     *
     * @return null|string string if any replacements were done
     * @throws \Twig_Error_Syntax
     */
    public function getReplacedContents(TemplateContext $context)
    {
        $templateName = $context->getTemplateName();
        $source = $this->loader->getSource($templateName);

        $stream = $this->lexer->tokenize(
            $source,
            $templateName
        );

        $rootNode = $this->parser->parse($stream);
        $rootNode->setAttribute(NodeAttributes::ATTRIBUTE_SOURCE, $source);

        $this->setRootNode($rootNode, $rootNode);


        $replacements = new SplStack();
        $this->handleNode($rootNode, $replacements, $context);

        if (count($replacements) === 0) {
            return null;
        }

        return $this->handleReplacements($source, $replacements);
    }

    protected function handleNode(Node $node, SplStack $replacements, TemplateContext $context)
    {
        foreach ($this->replacers as $replacer) {
            $replacedContent = $replacer->replace($node, $context);
            if ($replacedContent !== null) {
                $replacements->push(array('content' => $replacedContent, 'node' => $node));
                return;
            }
        }

        foreach ($node as $childNode) {
            if ($childNode !== null) {
                $this->handleNode($childNode, $replacements, $context);
            }
        }
    }

    protected function handleReplacements($source, SplStack $replacements)
    {
        foreach ($replacements as $data) {
            $replacement = $data['content'];
            /** @var Node $node */
            $node = $data['node'];
            $start = $this->nodeReplaceHelper->getStartPosition($node);
            $length = $this->nodeReplaceHelper->getLength($node);
            $source = substr_replace($source, $replacement, $start, $length);
        }

        return $source;
    }

    protected function setRootNode(Node $parentNode, Node $rootNode)
    {
        /** @var Node $childNode */
        foreach ($parentNode as $childNode) {
            if ($childNode === null) {
                continue;
            }

            $childNode->setAttribute(NodeAttributes::ATTRIBUTE_ROOT_NODE, $rootNode);
            $childNode->setAttribute(NodeAttributes::ATTRIBUTE_PARENT_NODE, $parentNode);
            $this->setRootNode($childNode, $rootNode);
        }
    }
}
