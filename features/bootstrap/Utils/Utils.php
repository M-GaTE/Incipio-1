<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Incipio\Tests\Behat\Utils;

/**
 * Class Utils: some Behat helpers not specific to a context.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Utils
{
    /**
     * Explode the given string with the given separator and build a tree out of it before merging it with the
     * existing tree.
     *
     * Warning: this function extensively depends on ::array_merge_recursive(). Hence when the existing tree has a
     * key also present in the array builded from the given string, the values will stack instead of behing replaced.
     *
     * @example
     *  // without an existing tree
     *  ::buildTree('_', 'an_example', $value)
     *  => [
     *      'an' => [
     *          'example' => $value
     *     ]
     *  ]
     *
     *  // with existing array
     *  ::buildTree(
     *      '_',
     *      'an_example',
     *      $value,
     *      [
     *          'an' => [
     *              'key1' => 'val1'
     *          ],
     *          'anotherKey' => 'blabla'
     *      ]
     *  )
     *  => [
     *      'an' => [
     *          'key1' => val1,
     *          'example' => $value
     *      ],
     *      'anotherKey' => 'blabla'
     *  ]
     *
     *  // with existing array with overlaping values
     *  ::buildTree(
     *      '_',
     *      'an_key1_c',
     *      $value,
     *      [
     *          'an' => [
     *              'key1' => 'val1'
     *          ],
     *          'anotherKey' => 'blabla'
     *      ]
     *  )
     *  => [
     *      'an' => [
     *          'key1' => [
     *              0   => val1,
     *              'c' => $value
     *          ]
     *      ],
     *      'anotherKey' => 'blabla'
     *  ]
     *
     * @param string $separator
     * @param string $string
     * @param mixed  $value
     * @param array  $tree
     *
     * @return array
     */
    public static function buildTree($separator, $string, $value, array $tree = [])
    {
        $stringArray = self::recursiveExplode($separator, $string, $value);

        return array_merge_recursive($tree, $stringArray);
    }

    /**
     * Extend the PHP ::explode() function to build a multidimensional array instead of a flattened one. If a element
     * uses the array annotation `[index]`, this part will be build as an array.
     *
     * @example
     *  ::recursiveExplode('_', 'a_b_c', $value)
     *  => [
     *      'a' => [
     *          'b' => [
     *              'c' => $value
     *          ]
     *      ]
     *  ]
     *
     *  ::recursiveExplode('_', 'a_b[0]_c', $value)
     *  => [
     *      'a' => [
     *          'b' => [
     *              0 => [ 'c' => $value ]
     *          ]
     *      ]
     *  ]
     *
     *  ::recursiveExplode('_', 'a_b[index]_c', $value)
     *  => [
     *      'a' => [
     *          'b' => [
     *              'index' => [ 'c' => $value ]
     *          ]
     *      ]
     *  ]
     *
     * @param string $separator
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function recursiveExplode($separator, $key, $value)
    {
        $tree = [];
        $explodedString = explode($separator, $key);

        /*
         * $explodedString = [
         *  'a',
         *  'b',
         *  'c',
         * ]
         */
        $currentArray = null;
        foreach ($explodedString as $index => $keyPart) {
            $indexPart = preg_grep('/a/', $keyPart);

            if (0 === $index) {
                $tree[$keyPart] = null;
                $currentArray = &$tree[$keyPart];
                continue;
            }

            if (false === isset($explodedString[$index + 1])) {
                $currentArray[$keyPart] = $value;
                continue;
            }

            $currentArray[$keyPart] = null;
            $currentArray = &$currentArray[$keyPart];
        }

        return $tree;
    }
}
