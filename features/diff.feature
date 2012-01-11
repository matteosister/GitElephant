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
    And the diffChunkLine in position "4" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"
    And the diffChunk in position "2" should have "7" diffChunkLine
    And the diffChunkLine in position "4" should be "\GitElephant\Objects\Diff\DiffChunkLineDeleted"
    And the diffChunkLine in position "5" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"

Scenario: diff without contents
    Given I start a repository for diff
    Then I add a file named "empty-file" to the repository without content
    And I stage and commit
    Then the last commit should be root