Feature: Test that orphaned media is deleted.

  Scenario: Command detects orphaned media
    Given a WP install
    And the following uploads:
      | filename           | is_orphaned |
      | 2018/06/test.jpg   | 0           |
      | 2018/06/orphan.jpg | 1           |


    When I run `wp media remove-orphans`
    Then STDOUT should contain:
      """
      2018/06/orphan.jpg
      """
    And the wp-content/uploads/2018/06/orphan.jpg file should exist

  Scenario: Command can be run with --cleanup
    Given a WP install
    And the following uploads:
      | filename           | is_orphaned |
      | 2018/06/test.jpg   | 0           |
      | 2018/06/orphan.jpg | 1           |


    When I run `wp media remove-orphans --cleanup`
    Then STDOUT should contain:
      """
      2018/06/orphan.jpg
      """
    And the wp-content/uploads/2018/06/orphan.jpg file should not exist
