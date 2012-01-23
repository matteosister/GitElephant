Feature: Repository Class
  In order to see the git repository structure
  As an API user
  I need to be able to interact with the git repository with an abstraction layer

Scenario: stage and commit functions
  Given I am in a folder
  And The repository has the methods
    """
    init
    stage
    commit
    """
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
  And The repository has the methods
    """
    deleteBranch
    createBranch
    getBranches
    getMainBranch
    """
  Then Method should get an array of "getBranches" "GitElephant\Objects\TreeBranch"
  When I create the branch "branch2" from "master"
  Then Method should get a count of "getBranches" 2
  When I delete the branch "branch2"
  Then Method should get a count of "getBranches" 1

Scenario: getMainBranch function
  Given I start a test repository
  And The repository has the method "getMainBranch"
  Then Method "getMainBranch" should get an object of type "GitElephant\Objects\TreeBranch"
  And Method should get an object with attribute "getMainBranch" "getName" "master"

Scenario: tag list, add and delete
  Given I start a test repository
  And The repository has the methods
    """
    deleteTag
    createTag
    getTags
    """
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
  When I get tree for a branch object "master"
  Then I should get a tree object
  When I create a tag "tag-test"
  And I get tree for a tag object "tag-test"
  Then I should get a tree object

Scenario: checkouts
  Given I start a test repository
  When I create the branch "branch2" from "master"
  And I checkout "branch2"
  And I add a file named "test-file-branch2"
  And I stage and commit with message "commit branch2"
  When I get tree for a branch object "master"
  Then Tree should get a count of 1
  When I get tree for a branch object "branch2"
  Then Tree should get a count of 2
  And I checkout "master"
  When I get tree for the main branch
  Then Tree should get a count of 1
  And I checkout "branch2"
  When I get tree for the main branch
  Then Tree should get a count of 2

Scenario: commit
  Given I start a test repository
  And The repository has the method "getCommit"
  When I call getCommit
  Then The commit should have the methods
    """
    getSha
    getParents
    getAuthor
    getCommitter
    getMessage
    getTree
    getDatetimeAuthor
    getDatetimeCommitter
    """
  And the commit should have not null values
    """
    getSha
    getAuthor
    getCommitter
    getTree
    getDatetimeAuthor
    getDatetimeCommitter
    """
  Then Commit message should not be an empty array

Scenario: clone
  Given I am in a folder
  And I clone "git://github.com/matteosister/GitElephant.git"
  Then Method "getCommit" should get an object of type "GitElephant\Objects\Commit"



