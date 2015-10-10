<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Security\Http\Authentication;

use FOS\UserBundle\Model\UserInterface;
use FrontBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @coversDefaultClass FrontBundle\Security\Http\Authentication\AuthenticationSuccessHandler
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class AuthenticationSuccessHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Test that on authentication success, a RedirectResponse is returned and that the API token has been
     * put into the User session.
     */
    public function testOnSuccessHandling()
    {
        $session = new Session(new MockArraySessionStorage(), new AttributeBag(), new FlashBag());
        $request = $this->prophesize(Request::class);
        $request->getSession()->willReturn($session);
        $request->get('_target_path', null, true)->willReturn('/toto');

        $httpUtils = $this->prophesize(HttpUtils::class);
        $httpUtils->createRedirectResponse($request->reveal(), '/toto')->willReturn(new RedirectResponse('/toto', 302));

        $user = $this->prophesize(UserInterface::class);
        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user->reveal());
        $jwtManager = $this->prophesize(JWTManager::class);
        $jwtManager->create($user)->willReturn('AnApiToken');

        $handler = new AuthenticationSuccessHandler($httpUtils->reveal());
        $handler->setJwtManager($jwtManager->reveal());

        $response = $handler->onAuthenticationSuccess($request->reveal(), $token->reveal());

        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertEquals('AnApiToken', $session->get('api_token'));
    }
}
