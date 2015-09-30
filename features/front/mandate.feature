@ignore
Feature: Filter users
  As an administrator
  I must be able to get the list of all mandates
  I must be able to filter or search on this list
  I should be able to view a mandate details
  I should be able to edit a mandate
  I should be able to create a new mandate
  I should be able to delete a mandate

  Background:
    Given I authenticate myself as admin

  Scenario: Access to the index page
    # Check for the table
    Given I am on "/mandates"
    Then I should see a paginated table "#mandates-index-table" with the columns:
      | #               |
      | Nom             |
      | Début de mandat |
      | Fin de mandat   |
      | Actions         |
    And I should see the row in the table "#mandates-index-table":
      | #  | Nom               | Début de mandat | Fin de mandat |
      | 12 | Mandate 2016/2018 | 09/2016         | 05/2018       |
      | 11 | Mandate 2015/2016 | 03/2015         | 04/2016       |

    # Check for the navigation links
    And I should see "Nouveau Mandat"
    When I follow "Nouveau Mandat"
    Then I am on "/mandates/new"


  Scenario: Access to a resource page
    Given I am on "/mandates/12"
    Then I should see "Mandat #12"
    And I should see "Nom"
    And I should see "Mandate 2016/2018"
    And I should see "Début de mandat"
    And I should see "09/2016"
    And I should see "Fin de mandat"
    And I should see "05/2018"

    # Check action buttons
    And I should see "Retour à la liste"
    And I should see "Éditer"
    And I should see "Supprimer"


  Scenario: Update the resource
    Given I am on "/users/12/edit"
#    TODO: enable this
#    And I should see "Retour à la liste"
#    And I should see "Annuler"
#    And I should see "Enregistrer"

    # With valid data
#    TODO: enable this
#    Then I fill in "front_mandate_name" with "Dummy name"
#    Then I fill in the following:
#      | front_mandate_name    | Dummy name |
#      | front_mandate_startAt | 10/2015    |
#      | front_mandate_endAt   | 10/2016    |
#    Then I press "Enregistrer"

  Scenario: Create a resource
#    TODO

  Scenario: Delete a resource
#    TODO
#    Given I am on "/users/12"
#    When I press "Supprimer"
#    Then I should be on "/users"
#    And I should see "Le mandat a bien été supprimé."


