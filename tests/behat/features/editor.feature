Feature: Editors can review draft records
  Editors work in the review workflow and can see records that are not yet public

  @api
  Scenario: An editor can open a draft commitment and sees that it is not published
    Given I am logged in as a user with the "editor" role
    When I go to "/en/commitments/kenya-mental-health-policy-child-adolescent-facilities"
    Then the response status code should be 200
    And I should see "This record is not published"

  @api
  Scenario: An editor sees the draft in the content overview
    Given I am logged in as a user with the "editor" role
    When I go to "/en/admin/content"
    Then the response status code should be 200

  @api
  Scenario: An editor approves a record in review and it becomes public
    Given I am logged in as a user with the "editor" role
    And there are no test commitments
    And an illustrative commitment is waiting in review
    When I go to "/en/commitments/behat-test-commitment"
    Then I should see "This record is not published"
    When I open the edit page of the test commitment
    And I select "Published" from "Change to"
    And I press "Save"
    And I go to "/en/commitments/behat-test-commitment"
    Then I should not see "This record is not published"
    And I should see "Behat test commitment"
    Then the test commitments are removed

  @api
  Scenario: An editor can reject a record in review and it stays hidden
    Given I am logged in as a user with the "editor" role
    And there are no test commitments
    And an illustrative commitment is waiting in review
    When I open the edit page of the test commitment
    And I select "Rejected" from "Change to"
    And I press "Save"
    And I go to "/en/commitments/behat-test-commitment"
    Then I should see "This record is not published"
    Then the test commitments are removed

  @api
  Scenario: An editor can open the data-quality report
    Given I am logged in as a user with the "editor" role
    When I go to "/admin/reports/data-quality"
    Then the response status code should be 200
    And I should see "Commitment records and outstanding items"
    And I should see "Kenya"
    And I should see "Indicator values by indicator and country"
    And I should see "Suicide mortality rate"

  @api
  Scenario: A contributor cannot open the data-quality report
    Given I am logged in as a user with the "contributor" role
    When I go to "/admin/reports/data-quality"
    Then the response status code should be 403
