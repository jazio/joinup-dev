@api
Feature: Collections Overview

  Scenario: Check visibility of "Collections" menu link.
    Given I am an anonymous user
    Then I should see the link "Collections"
    When I click "Collections"
    Then I should see the heading "Collections"
    # Check that all logged in users can see and access the link as well.
    Given I am logged in as a user with the "authenticated user" role
    Then I should see the link "Collections"
    When I click "Collections"
    Then I should see the heading "Collections"

  Scenario: View collection overview as an anonymous user
    Given users:
      | name          | mail                         | roles |
      | Madam Shirley | i.see.the.future@example.com |       |
    Given collections:
      | title             | description                    |
      | eHealth           | Supports health-related fields |
      | Open Data         | Facilitate access to data sets |
      | Connecting Europe | Reusable tools and services    |
    # Check page for authenticated users.
    When I am logged in as "Madam Shirley"
    And I am on the homepage
    And I click "Collections"
    Then I should see the text "eHealth"
    And I should see the text "Open Data"
    And I should see the text "Connecting Europe"

    When I am an anonymous user
    And I am on the homepage
    Then I should see the link "Collections"
    # @todo Anonymous users do not see new collections because the page cache
    # is not invalidated correctly.
    # @see ISAICP-2484
    # When I click "Collections"
    # Then I should see the link "eHealth"
    # And I should see the text "Supports health-related fields"
    # And I should see the link "Open Data"
    # And I should see the text "Facilitate access to data sets"
    # And I should see the link "Connecting Europe"
    # And I should see the text "Reusable tools and services"
    # When I click "eHealth"
    # Then I should see the heading "eHealth"
