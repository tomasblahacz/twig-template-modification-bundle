<?php

namespace Fixtures\Maba;

use Maba\Bundle\TwigTemplateModificationBundle\MabaTwigTemplateModificationBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Fixtures\Maba\Bundle\TestBundle\TestBundle;

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new FrameworkBundle(),
            new TwigBundle(),
            new MabaTwigTemplateModificationBundle(),
            new TestBundle(),
        );
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
