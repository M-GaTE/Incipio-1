<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new Csa\Bundle\GuzzleBundle\CsaGuzzleBundle(),
            new Dunglas\ApiBundle\DunglasApiBundle(),
            new Fidry\LoopBackApiBundle\LoopBackApiBundle(),
            new FOS\HttpCacheBundle\FOSHttpCacheBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
//            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

            new ApiBundle\ApiBundle(),
            new FrontBundle\Bundle\UserBundle\FrontUserBundle(),
            new FrontBundle\FrontBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Hautelook\AliceBundle\HautelookAliceBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
