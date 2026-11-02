Feature: The Commitments list pages through long result sets
  The list shows 20 records per page, so more than 20 must be reachable

  @api
  Scenario: With more than 20 published commitments the list has a second page
    Given I am logged in as a user with the "editor" role
    And 23 illustrative commitments are published
    When I go to "/en/tracker/commitments?search=Behat+paging+test"
    Then I should see "Behat paging test 01"
    And I should see "Behat paging test 20"
    And I should see "Behat paging test 11"
    And I should not see "Behat paging test 21"
    When I go to "/en/tracker/commitments?search=Behat+paging+test&page=1"
    Then I should see "Behat paging test 21"
    And I should see "Behat paging test 23"
    And I should not see "Behat paging test 01"
    And I should not see "Behat paging test 11"
    Then the paging test commitments are removed
