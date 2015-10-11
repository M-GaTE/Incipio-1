@job
Feature: Jobs management
  The job of a user is the job he has occupied for a given mandate
  A job may belong to a mandate
  At each new mandate, the jobs available are the job enabled
  A job may be left empty
  New jobs are created for the current mandate

  Background:
    Given the database is empty
    Given the fixtures file "authentication.yml" is loaded
    Given I authenticate myself as admin

  @crud
  Scenario: It should be possible to get all the jobs
    Given the fixtures file "job/collection.yml" is loaded
    When I send a GET request to "/api/jobs"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be greater than 11

  @crud
  Scenario: It should be possible to get a specific job
    Given the fixtures file "job/president.yml" is loaded
    When I send a GET request to "/api/jobs/1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON response should have the following nodes:
      | node             | value             | type   |
      | @context         | /api/contexts/Job |        |
      | @id              | /api/jobs/1       |        |
      | @type            | Job               |        |
      | abbreviation     | PR                |        |
      | enabled          | true              | bool   |
      | mandate          |                   | object |
      | mandate->@id     | /api/mandates/1   |        |
      | mandate->@type   | Mandate           |        |
      | mandate->endAt   |                   |        |
      | mandate->name    | Mandate 2005/2006 |        |
      | mandate->startAt |                   |        |
      | title            | President         |        |
      | users            |                   | array  |
      | users[0]         | /api/users/4      |        |
    And the JSON node "users" should have 1 element

  @crud
  Scenario: It should be possible to create a new job
    Given the fixtures file "job/mandate2005.yml" is loaded
    And the fixtures file "job/user-president.yml" is loaded
    When I send a POST request to "/api/jobs" with body:
    """
    {
      "title": "President",
      "abbreviation": "PR",
      "enabled": false,
      "mandate": "/api/mandates/1",
      "users": ["/api/users/1"]
    }
    """
    Then the response status code should be 201
    And the JSON response should have the following nodes:
      | node             | value             | type   |
      | @context         | /api/contexts/Job |        |
      | @id              | /api/jobs/1       |        |
      | @type            | Job               |        |
      | abbreviation     | PR                |        |
      | enabled          | false             | bool   |
      | mandate          |                   | object |
      | mandate->@id     | /api/mandates/1   |        |
      | mandate->@type   | Mandate           |        |
      | mandate->endAt   |                   |        |
      | mandate->name    | Mandate 2005/2006 |        |
      | mandate->startAt |                   |        |
      | title            | President         |        |
      | users            |                   | array  |
      | users[0]         | /api/users/1      |        |
    And the JSON node "users" should have 1 element

    When I send a GET request to "/api/jobs/1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON response should have the following nodes:
      | node             | value             | type |
      | @context         | /api/contexts/Job |        |
      | @id              | /api/jobs/1       |        |
      | @type            | Job               |        |
      | abbreviation     | PR                |        |
      | enabled          | false             | bool |
      | mandate          |                   | object |
      | mandate->@id     | /api/mandates/1   |        |
      | mandate->@type   | Mandate           |        |
      | mandate->endAt   |                   |        |
      | mandate->name    | Mandate 2005/2006 |        |
      | mandate->startAt |                   |        |
      | title            | President         |        |
      | users            |                   | array  |
      | users[0]         | /api/users/1      |        |
    And the JSON node "users" should have 1 element

  @crud
  Scenario: Data send for creating a job should be validated
    When I send a POST request to "/api/jobs" with body:
    """
    {
    }
    """
    Then the response status code should be 400
    And the response should be in JSON-LD
    And the JSON node "violations" should have 1 element
    And the JSON response should have the following nodes:
      | node                        | value                                 | type   |
      | @context                    | /api/contexts/ConstraintViolationList |        |
      | @type                       | ConstraintViolationList               |        |
      | hydra:title                 |                                       |        |
      | hydra:description           |                                       | array  |
      | violations                  |                                       | array  |
      | violations[0]               |                                       | object |
      | violations[0]->propertyPath | title                                 |        |
      | violations[0]->message      | Cette valeur ne doit pas être vide.   |        |

    When I send a GET request to "/api/jobs"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be greater than 0


  @crud
  Scenario: It should be possible to update a job
    Given the fixtures file "job/president.yml" is loaded
    When I send a PUT request to "/api/jobs/1" with body:
    """
    {
      "abbreviation": "Yo",
      "title": "Yolo"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON response should have the following nodes:
      | node             | value             | type   |
      | @context         | /api/contexts/Job |        |
      | @id              | /api/jobs/1       |        |
      | @type            | Job               |        |
      | abbreviation     | Yo                |        |
      | enabled          | true              | bool   |
      | mandate          |                   | object |
      | mandate->@id     | /api/mandates/1   |        |
      | mandate->@type   | Mandate           |        |
      | mandate->endAt   |                   |        |
      | mandate->name    | Mandate 2005/2006 |        |
      | mandate->startAt |                   |        |
      | title            | Yolo              |        |
      | users            |                   | array  |
      | users[0]         | /api/users/4      |        |
    And the JSON node "users" should have 1 element

    When I send a GET request to "/api/jobs/1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON response should have the following nodes:
      | node             | value             | type   |
      | @context         | /api/contexts/Job |        |
      | @id              | /api/jobs/1       |        |
      | @type            | Job               |        |
      | abbreviation     | Yo                |        |
      | enabled          | true              | bool   |
      | mandate          |                   | object |
      | mandate->@id     | /api/mandates/1   |        |
      | mandate->@type   | Mandate           |        |
      | mandate->endAt   |                   |        |
      | mandate->name    | Mandate 2005/2006 |        |
      | mandate->startAt |                   |        |
      | title            | Yolo              |        |
      | users            |                   | array  |
      | users[0]         | /api/users/4      |        |
    And the JSON node "users" should have 1 element

    When I send a GET request to "/api/mandates/1"
    Then the JSON node "jobs[0]" should be equal to "/api/jobs/1"

    When I send a GET request to "/api/users/4"
    Then the JSON node "jobs[0]->@id" should be equal to "/api/jobs/1"
    Then the JSON node "jobs[0]->abbreviation" should be equal to "Yo"
    Then the JSON node "jobs[0]->title" should be equal to "Yolo"

  @crud
  Scenario: It should be possible to delete a job.
    Given the fixtures file "job/president.yml" is loaded
    When I send a GET request to "/api/jobs/1"
    Then the JSON node "mandate->@id" should be equal to "/api/mandates/1"
    Then the JSON node "users[0]" should be equal to "/api/users/4"
    When I send a GET request to "/api/mandates/1"
    Then the JSON node "jobs[0]" should be equal to "/api/jobs/1"
    When I send a GET request to "/api/users/4"
    Then the JSON node "jobs[0]->@id" should be equal to "/api/jobs/1"

    When I send a DELETE request to "/api/jobs/1"
    Then the response status code should be 204
    And the JSON should be equal to:
    """
    """
    
    When I send a DELETE request to "/api/jobs/1"
    Then the response status code should be 404

    When I send a GET request to "/api/mandates/1"
    Then the JSON node "jobs" should have 0 element

    When I send a GET request to "/api/users/4"
    Then the JSON node "jobs" should have 0 element

  @filter
  Scenario: It should be possible to get all the enabled jobs.
    Given the fixtures file "job/collection-enabled.yml" is loaded
    When I send a GET request to "/api/jobs?filter[where][enabled]=1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 2
    And the JSON node "hydra:member[0]->enabled" should be equal to true
    And the JSON node "hydra:member[1]->enabled" should be equal to true

  @filter
  Scenario: It should be possible to order jobs by ID, title or abbreviation.
    Given the fixtures file "job/collection-order.yml" is loaded
    When I send a GET request to "/api/jobs?filter[order][id]=asc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/3"

    When I send a GET request to "/api/jobs?filter[order][id]=desc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/3"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/1"

    When I send a GET request to "/api/jobs?filter[order][title]=asc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/3"

    When I send a GET request to "/api/jobs?filter[order][title]=desc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/3"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/1"

    When I send a GET request to "/api/jobs?filter[order][abbreviation]=asc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/3"

    When I send a GET request to "/api/jobs?filter[order][abbreviation]=desc"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 3
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/3"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/api/jobs/1"

  @filter
  Scenario: It should be possible to find a job by its ID or title.
    Given the fixtures file "job/collection.yml" is loaded
    When I send a GET request to "/api/jobs?filter[where][id]=/api/jobs/1"
    Then the response status code should be 200
    And the response should be in JSON
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"

    When I send a GET request to "/api/jobs?filter[where][title]=President"
    Then the response status code should be 200
    And the response should be in JSON
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"

  @filter
  Scenario: It shoud be possible to find jobs by their abbreviation (an abbreviation may have several jobs).
    Given the fixtures file "job/collection.yml" is loaded
    When I send a GET request to "/api/jobs?filter[where][abbreviation]=PR"
    Then the response status code should be 200
    And the response should be in JSON
    And I should get a paged collection with the context "/api/contexts/Job"
    And the JSON node "hydra:totalItems" should be equal to 2
    And the JSON node "hydra:member[0]->@id" should be equal to "/api/jobs/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/api/jobs/2"

