Feature: Contributors add records for their assigned country
  A contributor can draft a commitment for their own country, cannot work on another country and cannot publish

  Background:
    Given I am logged in as a user with the "contributor" role and I have the following fields:
      | field_assigned_country | Kenya |
    And there are no test commitments
    When I go to "/en/node/add/commitment"
    Then the response status code should be 200

  @api
  Scenario: A contributor saves a draft commitment for their assigned country
    When I fill in "Title" with "Behat test commitment"
    And I select "Policy" from "Commitment type"
    And I select "Kenya" from "Country"
    And I select "Ministry of Health (Kenya)" from "Institution"
    And I fill in "Description" with "This record is created by an automated test to check that contributors can save drafts for their own country only."
    And I select "Other, see note" from "Licence"
    And I fill in "Source name" with "Automated test"
    And I check "Illustrative record"
    And I check "Policy and legislation"
    And I press "Save"
    Then the url should match "/commitments/behat-test-commitment"
    And I should see "Behat test commitment"
    And the test commitments are removed

  @api
  Scenario: A contributor cannot save a commitment for another country
    When I fill in "Title" with "Behat test commitment"
    And I select "Policy" from "Commitment type"
    And I select "Nigeria" from "Country"
    And I select "Federal Ministry of Health (Nigeria)" from "Institution"
    And I fill in "Description" with "This record is created by an automated test to check that contributors can save drafts for their own country only."
    And I select "Other, see note" from "Licence"
    And I fill in "Source name" with "Automated test"
    And I check "Illustrative record"
    And I check "Policy and legislation"
    And I press "Save"
    Then I should see "assigned country"
    And the url should match "/node/add/commitment"

  @api
  Scenario: A contributor cannot publish a commitment
    Then the "Save as" list should not offer "Published"
    And the "Save as" list should not offer "Rejected"
