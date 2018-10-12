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

  Scenario Outline: Admin adds and deletes config key in a app
    Given using OCS API version "<ocs-api-version>"
    When the administrator adds a config key "con" with value "conkey" in app "core" using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And the app "core" should have config key "con"
    And the config key "con" of app "core" must have value "conkey"
    When the administrator deletes the config key "con" in app "core" using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And the app "core" should not have config key "con"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns details about the app
    Given using OCS API version "<ocs-api-version>"
    And the app "user_management" has been enabled
    When the administrator requests the details about the app "user_management"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And the response should contain the installed version of the app
    And the response should have the name of the app "user_management"
    And the response should have the app enabled status of app
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |
