Feature: Test that orphaned media is deleted.

  Scenario: WP-CLI loads for your tests
    Given a WP install

    When I run `wp eval 'echo "Hello world.";'`
    Then STDOUT should contain:
      """
      Hello world.
      """
