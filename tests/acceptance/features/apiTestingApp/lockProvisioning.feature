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
    And user "Alice" has been created with default attributes and skeleton files
    When the administrator creates a lock for the file "textfile0.txt" with the type "1" for user "Alice"
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "Alice"
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
    And user "Alice" has been created with default attributes and skeleton files
    And the administrator has created a lock for the file "textfile0.txt" with the type "1" for user "Alice"
    When the administrator deletes the lock of the file "textfile0.txt" with the type "1" for user "Alice"
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 423        | 200         | OK                 |
      | 2               | 423        | 423         | Locked             |

  Scenario Outline: admin releases all locks of one type
    Given using OCS API version "<ocs-api-version>"
    And locking has been enabled
    And user "Alice" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and skeleton files
    And the administrator has created following locks
      | path          | type | user  |
      | textfile0.txt | 1    | Alice |
      | textfile1.txt | 1    | Brian |
      | textfile2.txt | 2    | Alice |
    When the administrator releases all locks of type "1"
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    When the administrator checks the lock for the file "textfile1.txt" with the type "1" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    When the administrator checks the lock for the file "textfile2.txt" with the type "2" for user "Alice"
    Then the HTTP status code should be "<http-status-failure>"
    And the HTTP reason phrase should be "<http-reason-phrase-failure>"
    And the OCS status code should be "<ocs-status-failure>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase | ocs-status-failure | http-status-failure | http-reason-phrase-failure |
      | 1               | 423        | 200         | OK                 | 100                | 200                 | OK                         |
      | 2               | 423        | 423         | Locked             | 200                | 200                 | OK                         |

  Scenario Outline: admin releases all locks
    Given using OCS API version "<ocs-api-version>"
    And locking has been enabled
    And user "Alice" has been created with default attributes and skeleton files
    And user "Brian" has been created with default attributes and skeleton files
    And the administrator has created following locks
      | path          | type | user  |
      | textfile0.txt | 1    | Alice |
      | textfile1.txt | 1    | Brian |
      | textfile2.txt | 2    | Alice |
    When the administrator releases all locks
    And the administrator checks the lock for the file "textfile0.txt" with the type "1" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    When the administrator checks the lock for the file "textfile1.txt" with the type "1" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    When the administrator checks the lock for the file "textfile2.txt" with the type "2" for user "Alice"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 423        | 200         | OK                 |
      | 2               | 423        | 423         | Locked             |
