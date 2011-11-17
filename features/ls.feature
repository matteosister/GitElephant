Feature: Repository Class
  In order to see the git repository structure
  As an API user
  I need to be able to interact with the git repository with an abstraction layer


Scenario: add function
  Given I am in a folder "test"
  And I init the repository
  And I add a file named "test-file"
  When I add to the repository "test-file"
  Then The status should contains "new file:   test-file"
  When I commit with message "test-commit"
  Then The status should contains "nothing to commit (working directory clean)"


Scenario: init function
  Given I am in a folder "test"
  And I init the repository
  And I add a file named "test-file"
  And I commit and stage with message "test-commit"
  Then I should get the status
    """
    # On branch master
    nothing to commit (working directory clean)
    """


Scenario: getTree method should return a tree object
  Given I am in a folder "test"
  And I init the repository
  And I add a file named "test-file"
  And I commit and stage with message "test-commit"
  When I get tree "refs/heads/master"
  Then I should get a tree object
