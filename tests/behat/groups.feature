@block @block_quickmail @block_quickmail_groups @javascript
Feature: Control student use of Quickmail in courses in accordance with FERPA constraints.
  In order to comply with FERPA restrictions
  As a student
  I should only be able to email members of my groups, but not others.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
      | student1 | Student   | 1        | student1@asd.com |
      | student2 | Student   | 2        | student2@asd.com |
      | student3 | Student   | 3        | student3@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
    And the following "groups" exist:
      | name | description | course | idnumber |
      | g1   | group1      | C1     | group1   |
      | g2   | group2      | C1     | group2   |
    And the following "group members" exist:
      | user     | group  |
      | student1 | group1 |
      | student2 | group2 |
      | student3 | group2 |
    And I am on the "Course 1" course page logged in as "teacher1"
    And I turn editing mode on
    When I add the "Quickmail" block
    Then I should see "Compose New Email" in the "Quickmail" "block"
    And I log out

  Scenario: Teacher sees all
    Given I am on the "Course 1" course page logged in as "teacher1"
    When I click on "Compose New Email" "link" in the "Quickmail" "block"
    Then I should see "Student 1 (g1)" in the "#from_users" "css_element"
    And I should see "Student 2 (g2)" in the "#from_users" "css_element"
    And I should see "Student 3 (g2)" in the "#from_users" "css_element"
    And I should not see "Teacher 1 (Not in a group)" in the "#from_users" "css_element"

  Scenario: Make sure students can't see other groups members
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I click on "Configuration" "link" in the "Quickmail" "block"
    And I set the following fields to these values:
      | Allow students to use Quickmail | Yes |
    And I press "Save changes"
    And I log out

    Given I am on the "Course 1" course page logged in as "student2"
    When I click on "Compose New Email" "link" in the "Quickmail" "block"
    Then I should not see "Student 1 (g1)" in the "#from_users" "css_element"
    And I should not see "Student 2 (g2)" in the "#from_users" "css_element"
    And I should not see "Teacher 1 (Not in a group)" in the "#from_users" "css_element"
    And I should see "Student 3 (g2)" in the "#from_users" "css_element"
