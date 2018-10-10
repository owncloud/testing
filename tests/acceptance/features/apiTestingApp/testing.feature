@api
Feature: Testing the testing app

  Scenario Outline: Testing app returns the location of server
    Given using OCS API version "<ocs-api-version>"
    When the administrator requests the system-info using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |