@user @ignore
Feature: User management
  As an administrator, I should be able to manage users.

  Background:
    Given I authenticate myself as admin

  Scenario: Get a collection
    When I send a GET request to "/api/users"
    Then the response status code should be 200
    And I should get a paged collection with the context "/api/contexts/User"

  Scenario: Get a resource
    When I send a GET request to "/api/users/1"
    Then the response status code should be 200
    And the JSON node "createdAt" should be a string
    And the JSON node "updatedAt" should be a string
    And the JSON node "jobs" should have 1 element
    And the JSON node "types" should have 1 element
    And the JSON node "roles" should have 2 elements
    Then the JSON response should have the following nodes:
      | node                               | value                                   | type    |
      | @context                           | /api/contexts/User                      |         |
      | @id                                | /api/users/1                            |         |
      | @type                              | User                                    |         |
      | createdAt                          |                                         |         |
      | endingSchoolYear                   | ~                                       |         |
      | fullname                           | Président TENDISERP                     |         |
      | jobs                               |                                         | array   |
      | jobs[0]                            |                                         | object  |
      | jobs[0]->@id                       | /api/jobs/1                             |         |
      | jobs[0]->@type                     | Job                                     |         |
      | jobs[0]->abbreviation              | PR                                      |         |
      | jobs[0]->mandate                   |                                         | object  |
      | jobs[0]->mandate->@id              | /api/mandates/12                        |         |
      | jobs[0]->mandate->@type            | Mandate                                 |         |
      | jobs[0]->mandate->endAt            |                                         |         |
      | jobs[0]->mandate->name             | Mandate 2016/2018                       |         |
      | jobs[0]->mandate->startAt          |                                         |         |
      | jobs[0]->title                     | President                               |         |
      | organizationEmail                  |                                         |         |
      | studentConvention                  |                                         | object  |
      | studentConvention->@id             | /api/student_conventions/PRSTEN20150711 |         |
      | studentConvention->@type           | StudentConvention                       |         |
      | studentConvention->dateOfSignature |                                         |         |
      | types                              |                                         | array   |
      | types[0]                           | TYPE_MEMBER                             |         |
      | updatedAt                          |                                         |         |
      | username                           | president.tendiserp                     |         |
      | email                              | president.tendiserp@incipio.fr          |         |
      | roles                              |                                         | array   |
      | roles[0]                           | ROLE_ADMIN                              |         |
      | roles[1]                           | ROLE_USER                               |         |
      | enabled                            | true                                    | boolean |

  Scenario: Create a new user
    When I send a POST request to "/api/users" with body:
      | username |  |


  Scenario: Update a resource


  Scenario: Filter users by type
    When I send a GET request to "/api/users?filter[where][type]=contractor"
    Then the response status code should be 200
    And I should get a paged collection with the context "/api/contexts/User"
    And the JSON node "hydra:totalItems" should be equal to 45
    And the JSON node "types" of the objects of the JSON node "hydra:member" should contains "TYPE_CONTRACTOR"

  Scenario: Filter users by mandate
    When I send a GET request to "/api/users?filter[where][mandate]=/api/mandates/5"
    Then the response status code should be 200
    And I should get a paged collection with the context "/api/contexts/User"
    And the JSON node "hydra:totalItems" should be equal to 6
    And all the users should have a mandate with the value "/api/mandates/5"

  Scenario: Delete a resource
    # Delete a resource that has a Job
    # Delete a resource that has a student convention
    When I send a DELETE request to "/api/users/1"
    Then the response status code should be 202
    # The method should be idempotent
    When I send a GET request to "/api/users/1"
    Then the response status code should be 404
