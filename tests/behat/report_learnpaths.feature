@ewallah @report @report_learnpaths
Feature: learnpaths report
  In order to understand what is going on in my Moodle site
  I need to be able to see where learnpaths were made

  Background:
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
      | Course 2 | C2        | 1                |
      | Course 3 | C3        | 1                |
      | Course 4 | C4        | 1                |
      | Course 5 | C5        | 1                |
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Student   | 1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
    And I log in as "admin"

    When I am on the "Course 2" "enrolment methods" page
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 1 |
    And I press "Add method"

    When I am on the "Course 3" "enrolment methods" page
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 2 |
    And I press "Add method"

    When I am on the "Course 4" "enrolment methods" page
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 3 |
    And I press "Add method"

    When I am on the "Course 5" "enrolment methods" page
    And I select "Course completed enrolment" from the "Add method" singleselect
    And I set the following fields to these values:
       | Course | Course 4 |
    And I press "Add method"

  @javascript
  Scenario: Show global learning path
    Given I navigate to "Reports > Learning paths" in site administration
    Then I should not see "Error"

  @javascript
  Scenario: Show learning path in course level
    Given I am on "Course 2" course homepage
    And I navigate to "Reports > Learning paths" in current page administration
    Then I should not see "Error"

  @javascript
  Scenario: Show learning path in user level
    Given I am on "Course 1" course homepage
    And I navigate to course participants
    And I follow "Student 1"
    When I follow "Learning paths"
    Then I should not see "Error"
