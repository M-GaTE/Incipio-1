<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Bundle\UserBundle\Tests;

use FOS\UserBundle\FOSUserBundle;
use FrontBundle\Bundle\UserBundle\FrontUserBundle;

/**
 * @coversDefaultClass FrontBundle\Bundle\UserBundle\FrontUserBundle
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FrontUserBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Check that the bundle builds properly and overrides FOSUserBundle
     */
    public function testBundle()
    {
        // Check that the bundle builds properly
        $bundle = new FrontUserBundle();

        // Check that the bundle overrides FOSUserBundle
        $fosUserBundle = new FOSUserBundle();
        $this->assertEquals($fosUserBundle->getName(), $bundle->getParent());
    }
}
