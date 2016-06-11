<?php

namespace Fixtures\Maba;

use Maba\Bundle\TwigTemplateModificationBundle\MabaTwigTemplateModificationBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new MabaTwigTemplateModificationBundle(),
            new \Fixtures\Maba\Bundle\TestBundle\TestBundle(),
        );
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}
