Feature: Editors can import indicator values from a CSV file
  The import form checks a file, reports each row, and loads valid rows unpublished

  @api
  Scenario: An editor checks a file without changing anything
    Given I am logged in as a user with the "editor" role
    And there are no test indicator values
    When I go to "/admin/content/import-indicator-values"
    And I attach the file "import-indicator-values.csv" to "files[file]"
    And I press "Check only"
    Then I should see "nothing was changed"
    And I should see "Would be loaded unpublished for review"
    And I should see "already exists"
    And I should see "is not in the tracker"
    And the test indicator value does not exist

  @api
  Scenario: An editor imports a file; valid rows are held unpublished and bad rows are reported
    Given I am logged in as a user with the "editor" role
    And there are no test indicator values
    When I go to "/admin/content/import-indicator-values"
    And I attach the file "import-indicator-values.csv" to "files[file]"
    And I press "Check and import"
    Then I should see "1 loaded unpublished"
    And I should see "1 skipped"
    And I should see "2 with errors"
    And the test indicator value exists but is not published
    Then the test indicator values are removed

  @api
  Scenario: A contributor cannot open the indicator import form
    Given I am logged in as a user with the "contributor" role
    When I go to "/admin/content/import-indicator-values"
    Then the response status code should be 403
