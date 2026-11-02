Feature: A visitor finds public commitments and indicators
  As a member of the public
  I want to see commitments and indicators with their sources
  So that I can check what governments have committed to

  Scenario: The Overview shows key figures, the map and its table alternative
    Given I am on "/en"
    Then I should see "Overview"
    And I should see "Countries tracked"
    And I should see "Where the tracker has data"
    And I should see "Countries at a glance"
    And I should see "Progress summary"

  Scenario: The site title spells out the full name
    Given I am on "/en"
    Then I should see "Child and Adolescent Mental Health Accountability Tracker"

  Scenario: The bare address goes to the English pages
    When I request "/" without following redirects
    Then the redirect should point to "/en"

  Scenario: A browser that prefers Spanish is sent to the Spanish pages
    Given my browser prefers "es-ES,es;q=0.9"
    When I request "/" without following redirects
    Then the redirect should point to "/es"

  Scenario: The Spanish Overview is in Spanish
    Given I am on "/es"
    Then I should see "Resumen"
    And I should see "Países en seguimiento"

  Scenario: The list shows the published commitment with its status
    Given I am on "/en/tracker/commitments"
    Then I should see "Mental Health Support Teams to reach national coverage"
    And I should see "On track"

  Scenario: Draft commitments are not listed
    Given I am on "/en/tracker/commitments"
    Then I should not see "Separate child and adolescent mental health facilities"

  Scenario: A filter that matches nothing shows a helpful message
    Given I am on "/en/tracker/commitments?year=1999"
    Then I should see "No commitments match these filters"

  Scenario: A filter shows as a removable chip
    Given I am on "/en/tracker/commitments?year=2025"
    Then I should see "Active filters"
    And I should see "Year: 2025"

  Scenario: The commitment page shows its source and licence
    Given I am on "/en/commitments/england-mental-health-support-teams-national-coverage-2030"
    Then I should see "Progress updates"
    And I should see "Source and licence"
    And I should see "Open Government Licence v3"
    And I should see "Evidence (gov.uk)"

  Scenario: The country page shows indicators with their sources
    Given I am on "/en/countries/kenya"
    Then I should see "Population ages 0-14, total"
    And I should see "Suicide mortality rate"
    And I should see "Data sources"

  Scenario: The Indicators page compares countries
    Given I am on "/en/tracker/indicators"
    Then I should see "Latest value by country"
    And I should see "Jordan"
    And I should see "South Africa"

  Scenario: Published data can be downloaded as CSV
    When I go to "/en/tracker/data/commitments.csv"
    Then the response status code should be 200
    And the response header "Content-Type" should contain "text/csv"
    And I should see "country_iso3"
    And I should see "United Kingdom"
    And I should not see "Kenya"
