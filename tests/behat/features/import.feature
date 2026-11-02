Feature: Editors can import commitments from a CSV file
  The import form checks a file, reports each row, and loads valid rows as Drafts

  @api
  Scenario: An editor checks a file without changing anything
    Given I am logged in as a user with the "editor" role
    And there are no test commitments
    When I go to "/admin/content/import-commitments"
    And I attach the file "import-commitments.csv" to "files[file]"
    And I press "Check only"
    Then I should see "nothing was changed"
    And I should see "Would be loaded as a Draft"
    And I should see "licence"
    When I go to "/en/commitments/behat-test-commitment"
    Then the response status code should be 404

  @api
  Scenario: An editor imports a file; valid rows become Drafts and bad rows are reported
    Given I am logged in as a user with the "editor" role
    And there are no test commitments
    When I go to "/admin/content/import-commitments"
    And I attach the file "import-commitments.csv" to "files[file]"
    And I press "Check and import"
    Then I should see "1 loaded as Drafts"
    And I should see "1 with errors"
    When I go to "/en/commitments/behat-test-commitment"
    Then I should see "This record is not published"
    Then the test commitments are removed

  @api
  Scenario: A contributor cannot open the import form
    Given I am logged in as a user with the "contributor" role
    When I go to "/admin/content/import-commitments"
    Then the response status code should be 403
