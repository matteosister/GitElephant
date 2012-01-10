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
    Then the last commit should be root
    And I add a file named "test-file2" to the repository with content
        """
        first line
        """
    Then the diff should have "1" object of mode "new_file"
    And the diffObject in position "1" should have "1" diffChunk
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        second line
        """
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should have "1" diffChunk
    And the diffChunk in position "1" should have "2" diffChunkLine
    And the diffChunkLine in position "1" should be "\GitElephant\Objects\Diff\DiffChunkLineUnchanged"
    And the diffChunkLine in position "2" should be "\GitElephant\Objects\Diff\DiffChunkLineAdded"
    Then I add a file named "test-file2" to the repository with content
        """
        first line
        """
    Then the diff should have "1" object of mode "index"
    And the diffObject in position "1" should have "1" diffChunk
    And the diffChunk in position "1" should have "2" diffChunkLine
    And the diffChunkLine in position "1" should be "\GitElephant\Objects\Diff\DiffChunkLineUnchanged"
    And the diffChunkLine in position "2" should be "\GitElephant\Objects\Diff\DiffChunkLineDeleted"