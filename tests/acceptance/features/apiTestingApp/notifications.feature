@api @app-required @notifications-app-required
Feature: Test notifications feature of testing app

  Background:
    Given app "notifications" has been enabled
    And these users have been created:
      | username |
      | user0    |
      | user1    |

  Scenario Outline: Testing app can create notifications for user
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates a notification with the following details using the testing API
      | key         | value                      |
      | subject     | lorem_subject              |
      | message     | lorem_message              |
      | user        | user0                      |
      | object_type | local_share                |
      | link        | www.lorem-notification.com |
      | object_id   | 47                         |
    Then user "user0" should have 1 notifications
    And the last notification of user "user0" should match these regular expressions
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
      | key         | value           |
      | user        | user0                      |
    And the administrator has created a notification with the following details using the testing API
      | key         | value           |
      | user        | user1                      |
    When user "user0" deletes all notifications using the testing API
    Then user "user0" should have 0 notifications
    And user "user1" should have 0 notifications
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |
