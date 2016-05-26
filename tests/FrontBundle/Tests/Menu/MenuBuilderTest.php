<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Menu;

use FrontBundle\Menu\MenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @coversDefaultClass FrontBundle\Menu\MenuBuilder
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $factory = $this->prophesize(FactoryInterface::class);
        $authorizationChecker = $this->prophesize(AuthorizationChecker::class);
        $tokenStorage = $this->prophesize(TokenStorage::class);

        new MenuBuilder($factory->reveal(), $authorizationChecker->reveal(), $tokenStorage->reveal());

        $this->assertTrue(true);
    }

    /**
     * @covers ::createHeaderMenu
     * @covers ::createOrganisationManagementMenu
     */
    public function testHeaderMenu()
    {
        $factory = new MenuFactory();
        $authorizationChecker = $this->prophesize(AuthorizationChecker::class);
        $tokenStorage = $this->prophesize(TokenStorage::class);

        $menuBuilder = new MenuBuilder($factory, $authorizationChecker->reveal(), $tokenStorage->reveal());

        $menu = $menuBuilder->createHeaderMenu();

        // Check menu tree
        $this->assertEquals('root', $menu->getName());
        $this->assertEquals(2, count($menu->getChildren()));
        $this->assertTrue($menu->hasChildren('dashboard'));
        $this->assertTrue($menu->hasChildren('organisation-management'));

        // Check Dashboard submenu tree
        $dashboard = $menu->getChild('dashboard');
        $this->assertEquals(0, count($dashboard->getChildren()));

        // Check organisation management tree
        $organisationManagement = $menu->getChild('organisation-management');
        $this->assertEquals(3, count($organisationManagement->getChildren()));
        $this->assertTrue($organisationManagement->hasChildren('users'));
        $this->assertEquals(0, count($organisationManagement->getChild('users')->getChildren()));
    }

    /**
     * @covers ::createUserMenu
     *
     * @TODO: cannot do it because need to override authorizationChecker->isGranted which does not work with prophecy
     * since this method is final.
     */
    public function testUserMenuWithLoggedUser()
    {
        $this->markTestSkipped('Cannot implement this test yet');
    }
}
