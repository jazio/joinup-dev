@api @email
Feature: Content Overview

  Scenario: Ensure access to content overview landing page, called "Keep up to date".
    Given I am an anonymous user
    # Anonymous users land on the homepage.
    Then I should see the link "Events, discussions, news ..."
    When I click "Events, discussions, news ..."
    # Visually hidden heading.
    Then I should see the heading "Keep up to date"
    # Check that all logged in users can see and access the overview page as well.
    # However, authenticated users land on their profile, so they need to use the menu.
    Given I am logged in as a user with the "authenticated user" role
    Then I should see the link "Keep up to date"
    When I click "Keep up to date"
    # Visually hidden heading.
    Then I should see the heading "Keep up to date"

  @terms
  Scenario: View content overview as an anonymous user
    Given users:
      | Username     | First name | Family name | E-mail               |
      | batbull      | Simba      | Hobson      | simba3000@hotmail.de |
      | welshbuzzard | Titus      | Nicotera    | nicotito@example.org |
      | hatchingegg  | Korinna    | Morin       | korimor@example.com  |
    And the following collections:
      | title             | description        | state     | moderation | featured |
      | Rumble collection | Sample description | validated | yes        | yes      |
    And "event" content:
      | title           | collection        | state     | created           |
      | Seventh Windows | Rumble collection | validated | 2018-10-03 4:21am |
    And "news" content:
      | title            | collection        | state     | author       | created           | featured |
      | The Playful Tale | Rumble collection | validated | batbull      | 2018-10-03 4:26am | yes      |
      | Night of Shadow  | Rumble collection | proposed  | welshbuzzard | 2018-10-03 4:26am | no       |
    And "document" content:
      | title             | collection        | state     | created           |
      | History of Flight | Rumble collection | validated | 2018-10-03 4:19am |
    And "discussion" content:
      | title            | collection        | state     | author      | created           | featured |
      | The Men's Female | Rumble collection | validated | hatchingegg | 2018-10-03 4:18am | yes      |

    # Check that visiting as a moderator does not create cache for all users.
    When I am logged in as a user with the "moderator" role
    And I am on the homepage
    And I click "Keep up to date"
    Then I should see the following facet items "Discussion, Document, Event, News" in this order
    And I should not see the following facet items "Collection"
    And I should see the following tiles in the correct order:
      | The Playful Tale  |
      | Seventh Windows   |
      | History of Flight |
      | The Men's Female  |
    Then I should see the "Seventh Windows" tile
    And I should see the "The Playful Tale" tile
    And I should see the "History of Flight" tile
    And I should see the "The Men's Female" tile
    And I should not see the "Rumble collection" tile
    And I should not see the "Night of Shadow" tile

    # The tiles for discussion and news entities should show the full name of
    # the author instead of the username.
    And I should see the text "Simba Hobson" in the "The Playful Tale" tile
    And I should see the text "Korinna Morin" in the "The Men's Female" tile

    When I click the "Document" content tab
    Then I should see the following facet items "Document, Discussion, Event, News" in this order
    And I should see the following tiles in the correct order:
      | History of Flight |

    # Check page for authenticated users.
    When I am logged in as a user with the "authenticated" role
    And I am on the homepage
    And I click "Keep up to date"
    Then I should see the "Seventh Windows" tile
    And I should see the "The Playful Tale" tile
    And I should see the "History of Flight" tile
    And I should see the "The Men's Female" tile
    But I should not see the "Rumble collection" tile
    And I should not see the "Night of Shadow" tile

    # Check the page for anonymous users.
    When I am an anonymous user
    And I am on the homepage
    Then I should see the link "Events, discussions, news ..."
    When I click "Events, discussions, news ..."
    Then I should see the "Seventh Windows" tile
    And I should see the "The Playful Tale" tile
    And I should see the "History of Flight" tile
    And I should see the "The Men's Female" tile
    But I should not see the "Rumble collection" tile
    And I should not see the "Night of Shadow" tile
    And the "My content" inline facet should allow selecting the following values "Featured content (2)"

    When I am logged in as "batbull"
    And I am on the homepage
    And I click "Keep up to date"
    Then the "My content" inline facet should allow selecting the following values "Featured content (2), My content (1)"
    When I click "My content" in the "My content" inline facet
    Then I should see the following tiles in the correct order:
      | The Playful Tale |
    But I should not see the "Seventh Windows" tile
    But I should not see the "History of Flight" tile
    But I should not see the "The Men's Female" tile
    When I click "All content" in the "My content" inline facet
    And I click "Featured content" in the "My content" inline facet
    Then I should see the following tiles in the correct order:
      | The Playful Tale  |
      | The Men's Female  |
    But I should not see the "History of Flight" tile
    And I should see the following facet items "Discussion, News" in this order
    And I should not see the following facet items "Collection"
    And I should not see the following facet items "Document"
    And I should not see the following facet items "Event"
