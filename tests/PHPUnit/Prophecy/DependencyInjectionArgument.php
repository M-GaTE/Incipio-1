<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Prophecy;

use Prophecy\Argument;
use Prophecy\Argument\Token\CallbackToken;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Extends {@see \Prophecy\Argument} for providing helpers for testing dependency injection elements.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DependencyInjectionArgument
{
    /**
     * Checks that the argument passed is an instance of Definition for the given class.
     *
     * @param string $class FQCN
     *
     * @return CallbackToken
     */
    public static function definition($class)
    {
        return Argument::that(function ($args) use ($class) {
            /* @var Definition $args */
            if (false === $args instanceof Definition) {
                return false;
            }

            $service = (new \ReflectionClass($args->getClass()))->newInstanceWithoutConstructor();

            return $service instanceof $class;
        });
    }

    /**
     * Checks that the argument passed is an instance of FileResource with the given resource.
     *
     * @param string $filePath
     *
     * @return CallbackToken
     */
    public static function service($filePath)
    {
        return Argument::that(function ($args) use ($filePath) {
            /* @var FileResource $args */
            if (false === $args instanceof FileResource) {
                return false;
            }

            return $filePath === $args->getResource();
        });
    }
}
