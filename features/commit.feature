Feature: Commit
    In order to manage commits
    As an API user
    I need to be able to interact with the git commits

Scenario: Commit
    Given I am in a folder
    And I init the repository
    And I add a file named "first-file"
    And I add to the repository "first-file"
    When I commit with message "first-commit"
    And I get the last commit
    Then the commit should have "0" parent
    And I add a file named "second-file"
    And I add to the repository "second-file"
    When I commit with message "second-commit"
    And I get the last commit
    Then the commit should have "1" parent

Scenario: Multiple Parents
    Given I am in a folder
    And I init the repository
    And I add a file named "first-file"
    And I add to the repository "first-file"
    And I commit with message "first-commit"
    Then I create the branch "branch2" from "master"
    And I checkout "branch2"
    And I add a file named "second-file"
    And I add to the repository "second-file"
    And I commit with message "second-commit"
