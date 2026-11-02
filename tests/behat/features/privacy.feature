Feature: Unreviewed records are never public
  Records that have not passed review, and their Spanish versions, must stay hidden

  Scenario: An anonymous visitor cannot open a draft commitment in English
    When I go to "/en/commitments/kenya-mental-health-policy-child-adolescent-facilities"
    Then the response status code should be 403

  Scenario: Nor in Spanish
    When I go to "/es/commitments/kenya-mental-health-policy-child-adolescent-facilities"
    Then the response status code should be 403

  Scenario: Draft records are not in the JSON download
    When I go to "/en/tracker/data/commitments.json"
    Then the response status code should be 200
    And I should not see "Kenya"
    And I should see "\"only_published_records\": true"

  Scenario: The read-only API does not list drafts to the public
    When I go to "/jsonapi/node/commitment"
    Then I should not see "Separate child and adolescent mental health facilities"

  Scenario: The API cannot be used to change data
    Given I am on "/jsonapi"
    Then the response status code should be 200
