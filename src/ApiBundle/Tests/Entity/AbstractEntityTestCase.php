<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\Entity;

use ApiBundle\Tests\FluentTestCaseInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PropertyAccess\StringUtil;

/**
 * Class EntityTestCase: test case for entities.
 *
 * @link   http://en.wikipedia.org/wiki/Fluent_interface
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
abstract class AbstractEntityTestCase extends KernelTestCase implements FluentTestCaseInterface
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $doctrineManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->doctrine = static::$kernel->getContainer()->get('doctrine');
        $this->doctrineManager = $this->doctrine->getManager();

        // Recreate a fresh database instance before each test case
        $metadata = $this->doctrineManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->doctrineManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    /**
     * {@inheritdoc}
     *
     * Note: this method is greatly inspired from Symfony Property Accessor {@see \Symfony\Component\PropertyAccessor}
     * and looks for hasser, removers and setters. It works on most cases but it still possible to have some edge cases
     * not handled. Symfony property accessor cannot be used since the goal of this test is to test the returned value,
     * which does no do Symfony PropertyAccessor.
     *
     * @coversNothing
     * @dataProvider fluentDataProvider
     */
    public function testFluentImplementation(array $data = [])
    {
        $reflClass = new \ReflectionClass($this->getEntityClassName());
        $entity = $reflClass->newInstanceArgs();

        $results = [];
        foreach ($data as $property => $value) {
            $camelized = $this->camelize($property);
            $singulars = (array) StringUtil::singularify($camelized);
            $methods = $this->findAdderAndRemover($reflClass, $singulars);

            if (null !== $methods) {
                $this->assertEquals(2, count($methods));
                $results[$methods[0]] = $entity->$methods[0]($value);
                $results[$methods[1]] = $entity->$methods[1]($value);
            } else {
                $setter = 'set'.$camelized;
                if ($this->isMethodAccessible($reflClass, $setter, 1)) {
                    $results[$setter] = $entity->$setter($value);
                }
            }
        }

        foreach ($results as $method => $returnedValue) {
            $this->assertEquals(
                $reflClass->name,
                get_class($returnedValue),
                sprintf('Expected %s to return a %s object, got %s instead.', $method, $reflClass->name, get_class($returnedValue))
            );
        }
    }

    /**
     * @testdox Test the entity property accessors (getters, setters, hassers, issers).
     *
     * @param array $data
     *
     * @return
     */
    abstract public function testPropertyAccessors(array $data = []);

    /**
     * Ensure that when the entity is deleted, the relations are properly unset.
     *
     * @coversNothing
     *
     * @param array $data
     *
     * @return
     */
    abstract public function testDeleteEntity(array $data = []);

    /**
     * Provides an optimal set of data for generating a complete entity.
     */
    abstract public function fluentDataProvider();

    /**
     * Camelizes a given string.
     *
     * @see \Symfony\Component\PropertyAccessor::camelize()
     *
     * @param string $string Some string
     *
     * @return string The camelized version of the string
     */
    private function camelize($string)
    {
        return strtr(ucwords(strtr($string, ['_' => ' '])), [' ' => '']);
    }

    /**
     * Searches for add and remove methods.
     *
     * @see \Symfony\Component\PropertyAccessor::findAdderAndRemover()
     *
     * @param \ReflectionClass $reflClass The reflection class for the given object
     * @param array            $singulars The singular form of the property name or null
     *
     * @return array|null An array containing the adder and remover when found, null otherwise
     */
    private function findAdderAndRemover(\ReflectionClass $reflClass, array $singulars)
    {
        foreach ($singulars as $singular) {
            $addMethod = 'add'.$singular;
            $removeMethod = 'remove'.$singular;

            $addMethodFound = $this->isMethodAccessible($reflClass, $addMethod, 1);
            $removeMethodFound = $this->isMethodAccessible($reflClass, $removeMethod, 1);

            if ($addMethodFound && $removeMethodFound) {
                return [$addMethod, $removeMethod];
            }
        }
    }

    /**
     * Returns whether a method is public and has the number of required parameters.
     *
     * @see \Symfony\Component\PropertyAccessor::isMethodAccessible()
     *
     * @param \ReflectionClass $class      The class of the method
     * @param string           $methodName The method name
     * @param int              $parameters The number of parameters
     *
     * @return bool Whether the method is public and has $parameters
     *              required parameters
     */
    private function isMethodAccessible(\ReflectionClass $class, $methodName, $parameters)
    {
        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);

            if ($method->isPublic()
                && $method->getNumberOfRequiredParameters() <= $parameters
                && $method->getNumberOfParameters() >= $parameters
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string Tested entity FQCN
     */
    abstract public function getEntityClassName();
}
