@api
Feature: get and set the last login date

  Background:
    Given these users have been created without skeleton files:
      | username |
      | Alice    |

  Scenario Outline: Get the last login date of a user
    Given using OCS API version "<ocs-api-version>"
    When the administrator gets the last login date for user "Alice" using the testing API
    Then the last login date in the response should be "0" days ago
    And the HTTP status code should be "<ocs-status-code>"
    And the OCS status code should be "<http-status-code>"
    Examples:
      | ocs-api-version | ocs-status-code | http-status-code |
      | 1               | 200             | 200              |
      | 2               | 200             | 200              |

  Scenario Outline: Set the last login date of a user
    Given using OCS API version "<ocs-api-version>"
    When the administrator sets the last login date for user "Alice" to "7" days ago using the testing API
    And the administrator gets the last login date for user "Alice" using the testing API
    Then the last login date in the response should be "7" days ago
    And the HTTP status code should be "<ocs-status-code>"
    And the OCS status code should be "<http-status-code>"
    Examples:
      | ocs-api-version | ocs-status-code | http-status-code |
      | 1               | 200             | 200              |
      | 2               | 200             | 200              |

  Scenario Outline: Try to get the last login for a non-existent user
    Given using OCS API version "<ocs-api-version>"
    When the administrator gets the last login date for user "Carol" using the testing API
    Then the OCS status message should be "user Carol not found"
    And the OCS status code should be "<ocs-status-code>"
    And the HTTP status code should be "<http-status-code>"
    Examples:
      | ocs-api-version | ocs-status-code | http-status-code |
      | 1               | 404             | 200              |
      | 2               | 404             | 404              |

  Scenario Outline: Try to set invalid last login date
    Given using OCS API version "<ocs-api-version>"
    When the administrator sets the last login date for user "Alice" to "<number_of_days>" days ago using the testing API
    Then the OCS status message should be "number of days is expected to be a positive integer"
    And the OCS status code should be "<ocs-status-code>"
    And the HTTP status code should be "<http-status-code>"
    Examples:
      | ocs-api-version | ocs-status-code | http-status-code | number_of_days |
      | 1               | 400             | 200              | -5             |
      | 1               | 400             | 200              | five days      |
      | 2               | 400             | 400              | -5             |
      | 2               | 400             | 400              | five days      |
