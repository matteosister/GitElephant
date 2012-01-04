Feature: Diff Class
  In order to see diffs
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