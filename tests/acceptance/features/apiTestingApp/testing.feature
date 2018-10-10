@api
Feature: Testing the testing app

  Scenario Outline: Testing app returns the location of server
    Given using OCS API version "<ocs-api-version>"
    When the administrator requests the system-info using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And the response should contain the server root
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns the logfile with given number of lines
    Given using OCS API version "<ocs-api-version>"
    When the administrator requests the logfile with <line-number> lines using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Then the response should contain <line-number> entries
    Examples:
      | ocs-api-version | line-number | ocs-status | http-status | http-reason-phrase |
      | 1               | 1          | 100        | 200         | OK                 |
      | 2               | 2          | 200        | 200         | OK                 |
