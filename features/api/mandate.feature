@mandate
Feature: Mandates management
  There is a mandate for every year.
  A mandate is composed of a group of users, although may not have any user.
  The job of a user is the job he has occupied for a given mandate.
  A job must belong to a mandate.
  At each new mandate, the jobs available are the job enabled.
  A job may be left empty.
  New jobs are created for the current mandate.
  A user may have one or several mandate, with or without a job.

  Background:
    Given the database is empty
    Given the fixtures file "authentication.yml" is loaded
    Given I authenticate myself as admin

  @crud
  Scenario: It should be possible to get all mandates
    Given the fixtures file "mandate/collection.yml" is loaded
    When I send a GET request to "/api/mandates"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And I should get a paged collection with the context "/api/contexts/Mandate"
    And the JSON node "hydra:totalItems" should be equal to 2

  @crud
  Scenario: It should be possible to get a specific mandate
    Given the fixtures file "mandate/collection.yml" is loaded
    When I send a GET request to "/api/mandates/1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON node "jobs" should have 3 element
    Then the JSON response should have the following nodes:
      | node     | value                     | type  |
      | @context | /api/contexts/Mandate     |       |
      | @id      | /api/mandates/1           |       |
      | @type    | Mandate                   |       |
      | endAt    | 2006-04-17T09:38:34+00:00 |       |
      | jobs     |                           | array |
      | jobs[0]  | /api/jobs/1               |       |
      | jobs[1]  | /api/jobs/2               |       |
      | jobs[2]  | /api/jobs/3               |       |
      | name     | Mandate 2005/2006         |       |
      | startAt  | 2005-06-25T16:43:30+00:00 |       |

  @crud
  Scenario: It should be possible to create a new mandate
    Given the fixtures file "mandate/job-president.yml" is loaded
    When I send a POST request to "/api/mandates" with body:
    """
    {
      "name": "My Mandate",
      "startAt": "2005-08-15T15:52:01+00:00",
      "endAt": "2005-12-15T15:52:01+00:00",
      "jobs": [ "/api/jobs/1" ]
    }
    """
    And the response status code should be 201
    And the response should be in JSON-LD
    And the JSON node "jobs" should have 1 element
    And the JSON response should have the following nodes:
      | node     | value                     | type    |
      | @context | /api/contexts/Mandate     |         |
      | @id      | /api/mandates/1           |         |
      | @type    | Mandate                   |         |
      | name     | My Mandate                |         |
      | startAt  | 2005-08-15T15:52:01+00:00 | string  |
      | endAt    | 2005-12-15T15:52:01+00:00 | string  |
      | jobs     |                           | array   |
      | jobs[0]  | /api/jobs/1               | array   |

    When I send a GET request to "/api/mandates/1"
    Then the response status code should be 200
    And the response should be in JSON-LD
    And the JSON node "jobs" should have 1 element
    Then the JSON response should have the following nodes:
      | node     | value                     | type    |
      | @context | /api/contexts/Mandate     |         |
      | @id      | /api/mandates/1           |         |
      | @type    | Mandate                   |         |
      | name     | My Mandate                |         |
      | startAt  | 2005-08-15T15:52:01+00:00 | string  |
      | endAt    | 2005-12-15T15:52:01+00:00 | string  |
      | jobs     |                           | array   |
      | jobs[0]  | /api/jobs/1               | array   |

  @crud
  Scenario: A mandate name is automatically picked up if none is given when creating a new mandate
    When I send a POST request to "/api/mandates" with body:
    """
    {
      "endAt": "2051-01-21",
      "startAt": "2050-01-26"
    }
    """
    Then the response status code should be 201
    And I should get a resource page with the context "/api/contexts/Mandate"
    And the JSON node "name" should be equal to "Mandate 2050/2051"

  @crud
  Scenario: Data send for creating a mandate should be validated
    When I send a POST request to "/api/mandates" with body:
    """
    {
    }
    """
    Then the response status code should be 400
    And the JSON node "violations" should have 2 element
    Then the JSON response should have the following nodes:
      | node                        | value                                 | type   |
      | @context                    | /api/contexts/ConstraintViolationList |        |
      | @type                       | ConstraintViolationList               |        |
      | hydra:title                 |                                       |        |
      | hydra:description           |                                       | array  |
      | violations                  |                                       | array  |
      | violations[0]               |                                       | object |
      | violations[0]->propertyPath | endAt                                 |        |
      | violations[0]->message      | Cette valeur ne doit pas être nulle.  |        |
      | violations[1]               |                                       | object |
      | violations[1]->propertyPath | startAt                               |        |
      | violations[1]->message      | Cette valeur ne doit pas être nulle.  |        |
