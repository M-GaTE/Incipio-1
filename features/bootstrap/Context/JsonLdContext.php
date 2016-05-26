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
use PHPUnit_Framework_Assert as PHPUnit;
use Sanpi\Behatch\Context\RestContext;
use Sanpi\Behatch\HttpCall\HttpCallResultPool;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JsonLdContext extends RestContext implements Context
{
    /**
     * @var JsonContext
     */
    protected $jsonContext;

    /**
     * @param string             $evaluationMode
     * @param HttpCallResultPool $httpCallResultPool
     */
    public function __construct($evaluationMode = 'javascript', HttpCallResultPool $httpCallResultPool)
    {
        $this->jsonContext = new JsonContext($evaluationMode, $httpCallResultPool);
    }

    /**
     * Check if the response is in JSON-LD. Is considered as JSON-LD response a valid JSON with the property
     * content-type header.
     *
     * @Then the response should be in JSON-LD
     */
    public function jsonLdResponse()
    {
        $this->theHeaderShouldBeEqualTo('content-type', 'application/ld+json');
        PHPUnit::assertNotNull(
            json_decode($this->getSession()->getDriver()->getContent()),
            'Expected response content to be JSON.'
        );
    }

    /**
     * Check if the response is an hydra paginated collection. 
     * 
     * @Then I should get a paged collection with the context :context
     *
     * @param string $context
     */
    public function iGetAPagedCollectionWithContext($context)
    {
        // Response should be in JSON-LD
        $this->jsonLdResponse();

        $this->jsonContext->theJsonNodeShouldBeEqualTo('@context', $context);
        $this->jsonContext->theJsonNodeShouldExist('@id');
        $this->jsonContext->theJsonNodeShouldBeEqualTo('@type', 'hydra:PagedCollection');
        $this->jsonContext->theJsonNodeShouldExist('hydra:totalItems');
        $this->jsonContext->theJsonNodeShouldExist('hydra:itemsPerPage');
        $this->jsonContext->theJsonNodeShouldExist('hydra:member');
        $this->jsonContext->theJsonNodeShouldExist('hydra:search');
    }

    /**
     * Check if the response is an hydra resource page.
     *
     * @Then I should get a resource page with the context :context
     *
     * @param string $context
     */
    public function iGetAResourcePageWithContext($context)
    {
        // Response should be in JSON-LD
        $this->jsonLdResponse();

        $this->jsonContext->theJsonNodeShouldBeEqualTo('@context', $context);
    }
}
