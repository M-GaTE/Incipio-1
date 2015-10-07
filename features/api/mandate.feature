@mandate @ignore
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
    Given I authenticate myself as admin


  Scenario: Get a collection
    When I send a GET request to "/api/mandates"
    Then the response status code should be 200
    And I should get a paged collection with the context "/api/contexts/Mandate"

  Scenario: Get a resource
    When I send a GET request to "/api/mandates/1"
    Then the response status code should be 200
    And the JSON node "jobs" should have 2 element
    Then the JSON response should have the following nodes:
      | node     | value                     | type  |
      | @context | /api/contexts/Mandate     |       |
      | @id      | /api/mandates/1           |       |
      | @type    | Mandate                   |       |
      | endAt    | 2007-03-18T10:25:58+00:00 |       |
      | jobs     |                           | array |
      | jobs[0]  | /api/jobs/4               |       |
      | jobs[1]  | /api/jobs/51              |       |
      | name     | Mandate 2005/2007         |       |
      | startAt  | 2005-11-27T17:41:35+00:00 |       |


  Scenario: Create a new resource
    # With valid data
    When I send a POST request to "/api/mandates" with body:
    """
    {
      "name": "Dummy date",
      "endAt": "2010-01-21T23:00:00+00:00",
      "startAt": "2009-01-26T23:00:00+00:00"
    }
    """
    Then the response status code should be 201
    And the JSON node "jobs" should have 0 element
    Then the JSON response should have the following nodes:
      | node     | value                     | type    |
      | @context | /api/contexts/Mandate     |         |
      | @id      | /api/mandates/13          |         |
      | @type    | Mandate                   |         |
      | endAt    | 2010-01-21T23:00:00+00:00 | string  |
      | jobs     |                           | array   |
      | name     | Dummy date                |         |
      | startAt  | 2009-01-26T23:00:00+00:00 | string  |

    # Check if the resource has been properly persisted
    When I send a GET request to "/api/mandates/13"
    Then the response status code should be 200
    And the JSON node "jobs" should have 0 element
    Then the JSON response should have the following nodes:
      | node     | value                     | type  |
      | @context | /api/contexts/Mandate     |       |
      | @id      | /api/mandates/1           |       |
      | @type    | Mandate                   |       |
      | endAt    | 2007-03-18T10:25:58+00:00 |       |
      | jobs     |                           | array |
      | name     | Mandate 2005/2007         |       |
      | startAt  | 2005-11-27T17:41:35+00:00 |       |

    # Post again with some other valid values but no name
    # Expect to have a name generated
    When I send a POST request to "/api/mandates" with body:
    """
    {
      "endAt": "2051-01-21",
      "startAt": "2050-01-26"
    }
    """
    Then the response status code should be 200
    And I should get a resource page with the context "/api/contexts/Mandate"
    And the JSON node "name" should be equal to "Mandate 2050/2051"

    # Test validation rules
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



#  Scenario: If one list all the mandates, there it at least one mandate: the current one.
#    #TODO
#
#  Scenario: A mandate may have users.
#    #TODO
#
#  Scenario: It should be possible to list all the mandates.
#    #TODO
#
#  Scenario: It should be possible to list all the members of a given mandate.
#    #TODO
#
#  Scenario: It should not be possible to create a mandate unless it starts at the current year. The ending date
#  should then be during the next year and may be omitted.
#    #TODO
#
#  Scenario: If not ending date is given for a mandate, it ends at the end of the next year.
#    #TODO
#
#  Scenario: If no new mandate has be created, a new one is automatically created and keep all the admin users as if
#  they have a new mandate.
#    #TODO
#
#  Scenario: It should not be possible to delete a mandate.
#    #TODO
#
#  Scenario: A new member can be added to a mandate even if the mandate already ended.
#    #TODO
#
#  Scenario: A member may be deleted from a mandate even if the mandate already ended.
#    #TODO
#
#  Scenario: Once a mandate created, the dates may change but must be of the same year.
#    #TODO
