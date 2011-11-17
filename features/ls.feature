Feature: Repository Class
  In order to see the git repository structure
  As an API user
  I need to be able to interact with the git repository with an abstraction layer

Scenario: stage and commit functions
  Given I am in a folder
  And The repository has the method "init"
  And The repository has the method "stage"
  And The repository has the method "commit"
  And I init the repository
  And I add a file named "test-file"
  When I add to the repository "test-file"
  Then The status should contains "new file:   test-file"
  When I commit with message "test-commit"
  Then The status should contains "nothing to commit (working directory clean)"

Scenario: init function
  Given I start a test repository
  And The repository has the method "getStatus"
  Then I should get the status
    """
    # On branch master
    nothing to commit (working directory clean)
    """

Scenario: branch list, add and delete
  Given I start a test repository
  And The repository has the method "deleteBranch"
  And The repository has the method "createBranch"
  And The repository has the method "getBranches"
  Then Method should get an array of "getBranches" "GitElephant\Objects\TreeBranch"
  When I create a branch from "branch2" "master"
  Then Method should get a count of "getBranches" 2
  When I delete the branch "branch2"
  Then Method should get a count of "getBranches" 1

Scenario: tag list, add and delete
  Given I start a test repository
  And The repository has the method "deleteTag"
  And The repository has the method "createTag"
  And The repository has the method "getTags"
  When I create a tag "tag-test"
  Then Method should get an array of "getTags" "GitElephant\Objects\TreeTag"
  Then Method should get a count of "getTags" 1
  When I delete a tag "tag-test"
  Then Method should get a count of "getTags" 0


Scenario: getTree method should return a tree object
  Given I start a test repository
  When I get tree "refs/heads/master"
  Then I should get a tree object
  And Tree should get a count of 1
