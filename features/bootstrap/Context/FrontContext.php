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

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FrontContext extends MinkContext implements Context
{
    /**
     * Fill login page for admin user.
     *
     * @Given I authenticate myself as admin
     */
    public function authenticateAs()
    {
        $this->visit('/login');
        $this->fillField('username', 'admin');
        $this->fillField('password', 'admin');
        $this->pressButton('_submit');
    }

    /**
     * @Given /^I should see a paginated table "(?P<element>[^"]*)" with the columns:$/
     *
     * @param string    $element
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeAPaginatedTableWithTheColumns($element, TableNode $table)
    {
        // Check that the (html) table exists
        $this->assertNumElements(1, $element);

        // Check that the (html) table has a pagination
        // Disabled for now, require JavaScript support to work
        //$this->assertNumElements(1, sprintf('%s_paginate', $element));

        // Get the (html) table rows
        $elements = $this->getSession()->getPage()->findAll('css', sprintf('%s thead > tr > th', $element));

        // Check the rows names
        foreach ($table->getRows() as $row) {
            $this->assertANodeElementContainsText($elements, $row[0]);
        }

        PHPUnit::assertEquals(
            count($table->getRows()),
            count($elements),
            'Expected to find the same number of columns.'
        );
    }

    /**
     * @Given /^I should see the row in the table "(?P<element>[^"]*)":$/
     *
     * @param string    $element
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheRow($element, TableNode $table)
    {
        // Check that the (html) table exists
        $this->assertNumElements(1, $element);

        // Get the (html) table rows
        $headerNodeElements = $this->getSession()->getPage()->findAll('css', sprintf('%s thead > tr > th', $element));
        $rowNodeElements = $this->getSession()->getPage()->findAll('css', sprintf('%s > tbody > tr', $element));

        foreach ($table->getColumnsHash() as $rowIndex => $row) {

            // At this point, we want to check that $rowNodeElements contains a rows matching $row
            $rowMatches = false;
            foreach ($rowNodeElements as $rowNodeElement) {
                /* @var NodeElement $rowNodeElement */
                try {
                    // Get the row cells
                    $cellNodeElements = $rowNodeElement->findAll('css', 'td');

                    // Check that for each cells of $row, we got a matching value
                    foreach ($row as $columnText => $cellValue) {
                        $this->assertNodeElementContainsText(
                            $cellNodeElements[$this->findTableIndex($headerNodeElements, $columnText)],
                            $cellValue
                        );
                    }

                    // At this point the row match otherwise an exception would have been thrown
                    $rowMatches = true;
                    continue;
                } catch (\Exception $exception) {
                    // Exception thrown because the row was not matching
                    // Do nothing and pass to the next row
                }
            }

            PHPUnit::assertTrue($rowMatches, sprintf('Expected to find at least one row matching row #%d', $rowIndex));
        }
    }

    /**
     * Is a debug helper, should not be left used in Behat features.
     *
     * @Then print the response
     */
    public function printTheResponse()
    {
        echo $this->getSession()->getPage()->getContent();
    }

    /**
     * Find the index of the column with the given text.
     *
     * @example
     *  The HTML table is:
     *  <table>
     *      <thead>
     *          <tr>
     *              <th>Col1</th>
     *              <th>Col2</th>
     *          </tr>
     *      </thead>
     *      <tbody>
     *          <tr>
     *              <td>CellA1<td>
     *              <td>CellA2<td>
     *          </tr>
     *          <tr>
     *              <td>CellB1<td>
     *              <td>CellB2<td>
     *          </tr>
     *      </tbody>
     *  </table>
     *
     *  Given the elements are the header:
     *  ::findTableIndex($elements, 'Col1')
     *  => 0
     *
     * @param NodeElement[] $elements
     * @param string        $columnText
     *
     * @return int
     *
     * @throws \Exception If no index is found
     */
    private function findTableIndex(array $elements, $columnText)
    {
        foreach ($elements as $index => $element) {
            try {
                $this->assertNodeElementContainsText($element, $columnText);

                return $index;
            } catch (\PHPUnit_Framework_AssertionFailedError $exception) {
                // Do nothing, pass to the next element
            }
        }

        throw new \Exception(sprintf('Expected to find a column with the text "%s"', $columnText));
    }

    /**
     * Check if one of the given nodes has the given text.
     *
     * @param NodeElement[] $elements
     * @param string        $text
     *
     * @throws \Exception
     */
    private function assertANodeElementContainsText(array $elements, $text)
    {
        foreach ($elements as $element) {
            PHPUnit::assertInstanceOf(
                NodeElement::class,
                $element,
                sprintf('Expected a %s element', NodeElement::class)
            );

            try {
                $this->assertNodeElementContainsText($element, $text);

                return;
            } catch (\PHPUnit_Framework_AssertionFailedError $exception) {
                // Does nothing
            }
        }

        // The given text has not been found, throw an error
        throw new \Exception(sprintf('No element contains the text "%s".', $text));
    }

    /**
     * Check if the given node has the given text.
     *
     * @param NodeElement $element
     * @param string      $text
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    private function assertNodeElementContainsText(NodeElement $element, $text)
    {
        $actual = $element->getText();
        $regex = '/'.preg_quote($text, '/').'/ui';

        $message = sprintf(
            'The text "%s" was not found in the text of the %s.',
            $text,
            $actual
        );

        PHPUnit::assertTrue((bool) preg_match($regex, $actual), $message);
    }
}
