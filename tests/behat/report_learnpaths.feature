@ewallah @report @report_learnpaths @javascript
Feature: learnpaths report
  In order to understand what is going on in my Moodle site
  I need to be able to see where learnpaths are made

  Background:
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1 | 1 |
      | Course 2 | C2 | 1 |
      | Course 3 | C3 | 1 |
      | Course 4 | C4 | 1 |
      | Course 5 | C5 | 1 |
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher | T | Teacher |
      | student | S | Student |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C1 | editingteacher |
      | teacher | C2 | editingteacher |
      | teacher | C3 | editingteacher |
      | teacher | C4 | editingteacher |
      | teacher | C5 | editingteacher |
      | student | C1 | student |
    And I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Disable" "link" in the "Guest access" "table_row"
    And I click on "Disable" "link" in the "Self enrolment" "table_row"
    And I click on "Disable" "link" in the "Cohort sync" "table_row"
    And I click on "Enable" "link" in the "Course completed enrolment" "table_row"

    When I am on "Course 2" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 1 |
    And I press "Add method"

    When I am on "Course 3" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 2 |
    And I press "Add method"

    When I am on "Course 4" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 3 |
    And I press "Add method"

    When I am on "Course 5" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 4 |
    And I press "Add method"
    And I log out

  Scenario: Learning path
    Given I am on the "C2" "Course" page logged in as "student"
    Then I should see "You will be enrolled in this course when"

  Scenario: When a course is completed, a user is auto enrolled into another course
    Given I am on the "C1" "Course" page logged in as "teacher"
    Then I should not see "You will be enrolled in this course when"
    And I am on "Course 2" course homepage
    Then I should not see "You will be enrolled in this course when"
