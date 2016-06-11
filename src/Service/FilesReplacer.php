<?php

namespace Maba\Bundle\TwigTemplateModificationBundle\Service;

use Maba\Bundle\TwigTemplateModificationBundle\Entity\TemplateContext;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Closure;
use InvalidArgumentException;

class FilesReplacer
{
    private $templateReplacer;
    private $directoryPatterns;

    public function __construct(TemplateReplacer $templateReplacer, array $directoryPatterns)
    {
        $this->templateReplacer = $templateReplacer;
        $this->directoryPatterns = $directoryPatterns;
    }

    public function replace(Closure $replaceCallable = null, Closure $noticeCallable = null)
    {
        /** @var Finder|SplFileInfo[] $finder */
        $finder = new Finder();

        foreach ($this->directoryPatterns as $pattern) {
            try {
                $finder->in($pattern);
            } catch (InvalidArgumentException $exception) {
                // ignore not found, as patterns can mismatch (for example subdirectories in app/Resources/views)
            }
        }

        $finder->files()->name('*.twig');

        foreach ($finder as $fileInfo) {
            $context = new TemplateContext();
            $context->setTemplateName($fileInfo->getRealPath());

            $contents = $this->templateReplacer->getReplacedContents($context);

            if ($noticeCallable !== null && count($context->getNotic/NodeReplaceHelperes()) > 0) {
                $noticeCallable($context->getNotices());
            }

            if ($contents !== null) {
                if ($replaceCallable !== null) {
                    $replaceCallable($fileInfo->getPathname(), $contents, $context->getNotices());
                }
                $fileInfo->openFile('w')->fwrite($contents);
            }
        }
    }
}
