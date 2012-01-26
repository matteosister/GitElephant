Feature: Diff Class
  In order to use Diff classes
  As an API user
  I need to be able to interact with the git repository and retrieve the diffs

Scenario: find diffs in a repository
    Given I start a repository for diff
    And I add a file named "test-file" to the repository with content
        """
        first line
        second line
        third line
        """
    And I stage and commit
    Then the last commit should be root
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        """
    And I stage and commit
    Then the diff should have "1" object of mode "new_file"
    And the diffObject in position "1" should have "1" diffChunk
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        second line
        """
    And I stage and commit
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should have "1" diffChunk
    And the diffChunk in position "1" should have "2" diffChunkLine
    And the diffChunkLine in position "1" should be "\GitElephant\Objects\Diff\DiffChunkLineUnchanged"
    And the diffChunkLine in position "2" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        """
    And I stage and commit
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should have "1" diffChunk
    And the diffChunk in position "1" should have "2" diffChunkLine
    And the diffChunkLine in position "1" should be "\GitElephant\Objects\Diff\DiffChunkLineUnchanged"
    And the diffChunkLine in position "2" should be "\GitElephant\Objects\Diff\DiffChunkLineDeleted"
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        2
        3
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13
        14
        15
        """
    And I stage and commit
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        2
        3 changed
        4
        5
        6
        7
        8
        9
        10
        11
        12
        13 changed
        14
        15
        """
    And I stage and commit
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should have "2" diffChunk
    And the diffChunk in position "1" should have "7" diffChunkLine
    And the diffChunkLine in position "3" should be "\GitElephant\Objects\Diff\DiffChunkLineDeleted"
    And the diffChunkLine in position "3" should have line number 3
    And the diffChunkLine in position "4" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"
    And the diffChunkLine in position "4" should have line number 3
    And the diffChunk in position "2" should have "7" diffChunkLine
    And the diffChunkLine in position "4" should be "\GitElephant\Objects\Diff\DiffChunkLineDeleted"
    And the diffChunkLine in position "4" should have line number 13
    And the diffChunkLine in position "5" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"
    And the diffChunkLine in position "5" should have line number 13

Scenario: handle diff renames
    Given I start a repository for diff
    And I add a file named "foo-file" to the repository with content
        """
        first line
        second line
        third line
        """
    And I stage and commit
    Then the diff should have "1" object of mode "new_file"
    Then I rename "foo-file" to "bar-file"
    And I stage and commit
    Then the diff should have "1" object of mode "renamed_file"
    And the diffObject in position "1" should be a rename from "foo-file" to "bar-file"
    And the diffObject in position "1" should have a similarity of "100" percent
    Then I rename "bar-file" to "baz-file"
    And I add a file named "baz-file" to the repository with content
        """
        first line
        second line
        third line
        fourth line
        """
    And I stage and commit
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should be a rename from "bar-file" to "baz-file"
    And the diffObject in position "1" should have a similarity of "73" percent

Scenario: diff without contents
    Given I start a repository for diff
    Then I add a file named "empty-file" to the repository without content
    And I stage and commit
    Then the last commit should be root