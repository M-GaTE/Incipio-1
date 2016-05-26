<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Context;

use ApiBundle\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use FOS\UserBundle\Doctrine\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use PHPUnit_Framework_Assert as PHPUnit;
use Sanpi\Behatch\Json\Json;
use Sanpi\Behatch\Json\JsonInspector;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ApiContext extends RawMinkContext implements Context, SnippetAcceptingContext, KernelAwareContext
{
    /*
     * Hook to implement KernelAwareContext
     */
    use KernelDictionary;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $manager;

    /** @var JWTManagerInterface */
    private $jwtManager;

    /** @var UserManager */
    private $userManager;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    /** @var JsonInspector */
    private $inspector;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata[]|array All class metadata registered by Doctrine.
     */
    private $metadata = [];

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     *
     * @param ManagerRegistry         $doctrine
     * @param JWTManagerInterface     $jwtManager
     * @param UserManager             $userManager
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        JWTManagerInterface $jwtManager,
        UserManager $userManager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->doctrine = $doctrine;
        $this->manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($this->manager);
        $this->metadata = $this->manager->getMetadataFactory()->getAllMetadata();
        $this->jwtManager = $jwtManager;
        $this->userManager = $userManager;
        $this->encoderFactory = $encoderFactory;
        $this->inspector = new JsonInspector('javascript');
    }

    /**
     * @param string $name User username or email.
     *
     * @return User
     *
     * @Transform :user
     */
    public function castToUser($name)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($name);
        PHPUnit::assertNotNull($user, sprintf('No user %s was found.', $name));

        return $user;
    }

    /**
     * @BeforeScenario @resetSession
     */
    public function resetSession()
    {
        $this->getSession()->reset();
        $client = $this->getSession()->getDriver()->getClient();
        $client->setServerParameter('HTTP_AUTHORIZATION', '');
    }

    /**
     * Authenticate a user via a JWT token.
     *
     * @param User $user
     *
     * @Given I authenticate myself as :user
     */
    public function authenticateAs(User $user)
    {
        $client = $this->getSession()->getDriver()->getClient();
        $token = $this->jwtManager->create($user);
        $client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));
    }

    /**
     * @Then the password for user ":username" should be ":password"
     *
     * @param $username
     * @param $password
     */
    public function thePasswordForUserShouldBe($username, $password)
    {
        $user = $this->userManager->findUserByUsername($username);
        if (null === $user) {
            throw new \InvalidArgumentException(sprintf('No user with username %s can be found', $username));
        }
        $encoder = $this->encoderFactory->getEncoder($user);
        $valid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
        PHPUnit::assertTrue($valid, sprintf('The password for user %s does not match %s', $username, $password));
    }

    /**
     * Is a debug helper, should not be left used in Behat features.
     *
     * @Then print the last response
     */
    public function printTheResponse()
    {
        $json = $this->getSession()->getPage()->getContent();
        echo json_encode(json_decode($json), JSON_PRETTY_PRINT);
    }

    /**
     * @Given all the users should have a mandate with the value :iri
     *
     * @param string $iri
     */
    public function allTheUsersShouldHaveAMandateWithTheValue($iri)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $json = $this->getSession()->getPage()->getContent();
        $users = $this->inspector->evaluate(new Json($json), 'hydra:member');

        foreach ($users as $user) {
            $count = 0;
            foreach ($user->jobs as $job) {
                if (isset($job->mandate)) {
                    if ($iri === $accessor->getValue($job->mandate, '@id')) {
                        ++$count;
                    }
                }
            }
            PHPUnit::assertGreaterThanOrEqual(
                1,
                $count,
                sprintf(
                    'Expected user %s to have at least one job with the mandate %s.',
                    $accessor->getValue($user, '@id'),
                    $iri
                )
            );
        }
    }

    /**
     * @Given /^the JSON node "([^"]*)" of the objects of the JSON node "([^"]*)" should contains "([^"]*)"$/
     * 
     * @param string $node
     * @param string $collection
     * @param string $arrayValue
     */
    public function theJSONNodeOfTheObjectsOfTheJSONNodeShouldContains($node, $collection, $arrayValue)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $json = $this->getSession()->getPage()->getContent();
        $collection = $this->inspector->evaluate(new Json($json), $collection);

        foreach ($collection as $element) {
            in_array($arrayValue, $accessor->getValue($element, $node));
        }
    }
}
