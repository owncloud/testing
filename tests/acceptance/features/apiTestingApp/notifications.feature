@api @app-required @notifications-app-required
Feature: Test notifications feature of testing app

  Background:
    Given app "notifications" has been enabled
    And these users have been created with default attributes and skeleton files:
      | username |
      | Alice    |
      | Brian    |

  Scenario Outline: Testing app can create notifications for user
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates a notification with the following details using the testing API
      | key         | value                      |
      | subject     | lorem_subject              |
      | message     | lorem_message              |
      | user        | Alice                      |
      | object_type | local_share                |
      | link        | www.lorem-notification.com |
      | object_id   | 47                         |
    Then user "Alice" should have 1 notifications
    And the last notification of user "Alice" should match these regular expressions about user "Alice"
      | key         | regex                          |
      | subject     | /^lorem_subject$/              |
      | message     | /^lorem_message$/              |
      | link        | /^www.lorem-notification.com$/ |
      | object_type | /^local_share$/                |
      | object_id   | /^47$/                         |
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can delete notifications
    Given using OCS API version "<ocs-api-version>"
    And the administrator has created a notification with the following details using the testing API
      | key  | value |
      | user | Alice |
    And the administrator has created a notification with the following details using the testing API
      | key  | value |
      | user | Brian |
    When user "Alice" deletes all notifications using the testing API
    Then user "Alice" should have 0 notifications
    And user "Brian" should have 0 notifications
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |
