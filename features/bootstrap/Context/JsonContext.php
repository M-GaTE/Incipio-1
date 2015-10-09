<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Incipio\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as PHPUnit;
use Sanpi\Behatch\Context\JsonContext as SampiJsonContext;
use Sanpi\Behatch\HttpCall\HttpCallResultPool;
use Sanpi\Behatch\Json\Json;
use Sanpi\Behatch\Json\JsonInspector;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JsonContext extends SampiJsonContext implements Context
{
    /**
     * @var JsonInspector
     */
    private $inspector;

    /**
     * {@inheritdoc}
     */
    public function __construct($evaluationMode = 'javascript', HttpCallResultPool $httpCallResultPool)
    {
        parent::__construct($evaluationMode, $httpCallResultPool);

        $this->inspector = new JsonInspector($evaluationMode);
    }

    /**
     * @Then the JSON node :node should be greater than :value
     *
     * @param string $node
     * @param string $value
     */
    public function theJsonNodeShouldBeHigher($node, $value)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertGreaterThanOrEqual(
            $value,
            $actual,
            sprintf('Expected `%s` to be greater than `%s`.', $value, $actual)
        );
    }

    /**
     * @Then the JSON node :node should be less than :value
     *
     * @param string $node
     * @param string $value
     */
    public function theJsonNodeShouldBeLower($node, $value)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertLessThanOrEqual(
            $value,
            $actual,
            sprintf('Expected `%s` to be less than `%s`.', $value, $actual)
        );
    }

    /**
     * @Then the JSON node :node should be an array
     *
     * @param string $node
     */
    public function theJsonNodeShouldBeAnArray($node)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertTrue(
            is_array($actual),
            sprintf('Expected node `%s` to be a JSON array.', $node)
        );
    }

    /**
     * @Then the JSON node :node should be an object
     *
     * @param string $node
     */
    public function theJsonNodeShouldBeAnObject($node)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertInstanceOf(
            'StdClass',
            $actual,
            sprintf('Expected node `%s` to be a JSON object.', $node)
        );
    }

    /**
     * @Then the JSON node :node should be null
     *
     * @param string $node
     */
    public function theJsonNodeShouldBeNull($node)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertNull($actual);
    }

    /**
     * @Then the JSON node :node should not be null
     *
     * @param string $node
     */
    public function theJsonNodeShouldNotBeNull($node)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertNotNull($actual);
    }

    /**
     * @Then the JSON node :node should be a string
     *
     * @param string $node
     */
    public function theJsonNodeShouldBeAString($node)
    {
        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        PHPUnit::assertTrue(is_string($actual));
    }

    /**
     * @Then the JSON response should should have the following nodes:
     *
     * @param TableNode $table
     */
    public function theJSONResponseIsComposedOf(TableNode $table)
    {
        $count = 0;
        foreach ($table->getColumnsHash() as $row) {
            ++$count;
            $value = $row['value'];

            // Check for null value
            // The `~` is used to specify null value unless the type is explicitely set to string
            if ('~' === $value
                && (false === isset($row['type']) || 'string' !== $row['type'])
            ) {
                $this->theJsonNodeShouldBeNull($row['node']);

                continue;
            }

            // Default type is set to string
            if (false === isset($row['type'])) {
                $row['type'] = 'string';
            }

            if ('bool' === $row['type'] || 'boolean' === $row['type']) {
                if ('false' === $value) {
                    $value = false;
                } else {
                    // if 'true' === $value will be true
                    $value = (bool) $value;
                }
                PHPUnit::assertEquals($value, $this->inspector->evaluate($this->getJson(), $row['node']));

                continue;
            } elseif ('int' === $row['type'] || 'integer' === $row['type']) {
                $value = (int) $value;
                PHPUnit::assertEquals($value, $this->inspector->evaluate($this->getJson(), $row['node']));

                continue;
            } elseif ('array' === $row['type']) {
                $this->theJsonNodeShouldBeAnArray($row['node']);

                continue;
            } elseif ('object' === $row['type']) {
                $this->theJsonNodeShouldBeAnObject($row['node']);

                continue;
            }

            // If want to compare to an empty string, the value must be `""` in the table
            // Otherwise an empty string means no check on the value
            if ('""' === $value) {
                $this->theJsonNodeShouldBeEqualTo($row['node'], '');
            } elseif ('' === $value) {
                $this->theJsonNodeShouldExist($row['node']);
            } else {
                $this->theJsonNodeShouldBeEqualTo($row['node'], $value);
            }
        }

        $nbrOfNodes = $this->getNumberOfNodes($this->getJson()->getContent());
        PHPUnit::assertEquals(
            $nbrOfNodes,
            $count,
            sprintf('Expected to find %d nodes. Found %d instead', $nbrOfNodes, $count)
        );
    }

    private function getNumberOfNodes($content)
    {
        $count = 0;

        if ($content instanceof \StdClass) {
            $content = (array) $content;
            $count += $this->getNumberOfNodes($content);
        } elseif (is_array($content)) {
            $count += count($content);
            foreach ($content as $element) {
                if (is_array($element) || $element instanceof \StdClass) {
                    $count += $this->getNumberOfNodes($element);
                }
            }
        }

        return $count;
    }

    /**
     * Helper to get the JSON object from the response.
     *
     * @return Json
     */
    private function getJson()
    {
        return new Json($this->getSession()->getPage()->getContent());
    }
}
