@ignore
Feature: Filter users
  As an administrator
  I must be able to list all users and filter them

  Background:
    Given I authenticate myself as admin

  Scenario: Access to the users page
    Given I am on "/users"
    Then I should see "Nouvel utilisateur"
    And I should see an "table" element
    And I should see "Admin NIMDA"
    And I should see "Ca AC"
    And I fill in the following:
      | front_user_filtering_mandate_id | /api/mandates/12 |
      | front_user_filtering_user_type  | TYPE_MEMBER      |
    And I press "Filtrer"
    Then I should be on "/users/"
    Then I should see "Président TENDISERP"
    Then I should not see "Admin NIMDA"

  Scenario: Access to a user page
    Given I am on "/users/30"
    Then I should see "#30 Anouk Leconte"
    And I should see "Retour à la liste"
    And I should see "Éditer"
    And I should see "Andre92"
    And I should see "Charlotte45@noos.fr"
    And I should see "admin"
    And I should see "2015"
    And I should see "Innovation Connector"
    And I should see "User Experience Directress"
    And I should see "01/01/2014"
    And I should see "09/06/2015"

  Scenario: Access to the edit page
    Given I am on "/users/30"
    When I press "Éditer"
    Then I should be on "/users/30/edit"
    When I press "Annuler"
    When I should be on "/users/30"
