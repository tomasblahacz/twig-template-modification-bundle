<?php

namespace Maba\Tests;

use Composer\Autoload\ClassLoader;
use Maba\Bundle\TwigTemplateModificationBundle\Service\NodeReplaceHelper;
use Maba\Tests\Fixtures\ComplexNodeReplacer;
use Maba\Tests\Fixtures\UppercaseNodeReplacer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Maba\Bundle\TwigTemplateModificationBundle\Factory\ReplacerFactory;
use Maba\Bundle\TwigTemplateModificationBundle\Service\FilesReplacer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SplFileInfo;

class FunctionalTest extends KernelTestCase
{
    /**
     * @var FilesReplacer
     */
    private $replacer;

    protected static function getKernelClass()
    {
        return 'Fixtures\Maba\TestKernel';
    }

    protected function setUp()
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/Fixtures/tmp');

        $filesystem->mirror(__DIR__ . '/Fixtures/template', __DIR__ . '/Fixtures/tmp');

        $loader = new ClassLoader();
        $loader->addPsr4('Fixtures\\Maba\\Bundle\\', __DIR__ . '/Fixtures/tmp/src');
        $loader->addPsr4('Fixtures\\Maba\\', __DIR__ . '/Fixtures/tmp/app');
        $loader->register(true);

        static::bootKernel();
        $container = static::$kernel->getContainer();
        /** @var ReplacerFactory $factory */
        $factory = $container->get('maba_twig_template_modification.factory.files_replacer');
        /** @var NodeReplaceHelper $nodeReplaceHelper */
        $nodeReplaceHelper = $container->get('maba_twig_template_modification.node_replace_helper');
        $this->replacer = $factory->createFilesReplacer([
            new UppercaseNodeReplacer(),
            new ComplexNodeReplacer($nodeReplaceHelper),
        ]);
    }
    
    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/Fixtures/tmp');
    }

    public function testReplacing()
    {
        $this->replacer->replace();

        $expectedDir = realpath(__DIR__ . '/Fixtures/expected');
        $realDir = realpath(__DIR__ . '/Fixtures/tmp');

        /** @var Finder|SplFileInfo[] $finder */
        $finder = new Finder();
        $finder->in($expectedDir)->files()->name('*.twig');

        foreach ($finder as $fileInfo) {
            $this->assertFileEquals(
                $fileInfo->getRealPath(),
                $realDir . substr($fileInfo->getRealPath(), strlen($expectedDir))
            );
        }
    }
}
