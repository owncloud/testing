@api
Feature: add and delete locks on files

  Scenario Outline: enable lock provisioning
    Given using OCS API version "<ocs-api-version>"
    And locking has been enabled
    When the administrator checks the lock provisioning status using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: disable lock provisioning
    Given using OCS API version "<ocs-api-version>"
    And locking has been disabled
    When the administrator checks the lock provisioning status using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 501        | 200         | OK                 |
      | 2               | 501        | 501         | Not Implemented    |

  Scenario Outline: admin locks a file for a user
    Given using OCS API version "<ocs-api-version>"
    And locking has been enabled
    And user "user0" has been created
    When the administrator creates a lock for the file "textfile0.txt" with the type "1" for user "user0"
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "user0"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: admin deletes lock from a file for a user
    Given using OCS API version "<ocs-api-version>"
    And locking has been enabled
    And user "user0" has been created
    And the administrator has created a lock for the file "textfile0.txt" with the type "1" for user "user0"
    When the administrator deletes the lock of the file "textfile0.txt" with the type "1" for user "user0"
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "user0"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 423        | 200         | OK                 |
      | 2               | 423        | 423         | Locked             |
