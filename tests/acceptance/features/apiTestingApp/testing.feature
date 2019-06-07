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

  Scenario Outline: Testing app can delete the logfile
    Given using OCS API version "<ocs-api-version>"
    Then log entries should decrease when the administrator clears the logfile
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Admin adds and deletes config key in a app
    Given using OCS API version "<ocs-api-version>"
    When the administrator adds a config key "con" with value "conkey" in app "core" using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And app "core" should have config key "con"
    And the config key "con" of app "core" should have value "conkey"
    When the administrator deletes the config key "con" in app "core" using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And app "core" should not have config key "con"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Admin adds multiple config keys
    Given using OCS API version "<ocs-api-version>"
    When the administrator adds these config keys using the testing API
      | appid           | configkey   | value     |
      | core            | key1        | value1    |
      | user_management | key2        | value2    |
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And following config keys should exist
      | appid           | configkey   |
      | core            | key1        |
      | user_management | key2        |
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Admin deletes multiple config keys
    Given using OCS API version "<ocs-api-version>"
    And the administrator has added these config keys
      | appid           | configkey   | value     |
      | core            | key1        | value1    |
      | user_management | key2        | value2    |
    When the administrator deletes these config keys using the testing API
      | appid           | configkey   |
      | core            | key1        |
      | user_management | key2        |
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And following config keys should not exist
      | appid           | configkey   |
      | core            | key1        |
      | user_management | key2        |
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns details about the app
    Given using OCS API version "<ocs-api-version>"
    Given app "comments" has been enabled
    When the administrator requests the details about the app "comments"
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And the response should contain the installed version of the app
    And the response should have the name of the app "comments"
    And the response should have the app enabled status of app
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app can change the max file id length
    Given using OCS API version "<ocs-api-version>"
    When the administrator increases the max file id size beyond 32 bits using the testing API
    And the administrator creates user "user0" using the provisioning API
    Then file "/textfile0.txt" should have file id greater than 32 bits for user "user0"
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can run occ commands
    Given using OCS API version "<ocs-api-version>"
    And app "comments" has been enabled
    And app "notifications" has been disabled
    When the administrator runs these occ commands using the testing API
      | command                             |
      | app:disable comments                |
      | app:enable notifications            |
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And app "comments" should be disabled
    And app "notifications" should be enabled
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns all the extensions of a mime type
    Given using OCS API version "<ocs-api-version>"
    When the administrator gets all the extensions of mime-type "audio" using the testing API
    Then the extensions returned should be "flac, m4a, m4b, mp3, m3u, m3u8, oga, ogg, opus, pls, wav"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the HTTP status code should be "<http-status>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns all the extensions of a mime type with subtype
    Given using OCS API version "<ocs-api-version>"
    When the administrator gets all the extensions of mime-type "audio/ogg" using the testing API
    Then the extensions returned should be "oga, ogg, opus"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the HTTP status code should be "<http-status>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns success if the given apache module is loaded
    Given using OCS API version "<ocs-api-version>"
    When the administrator checks if "core" apache module is installed using the testing API
    Then the HTTP reason phrase should be "<http-reason-phrase>"
    And the HTTP status code should be "<http-status>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 100        | 200         | OK                 |
      | 2               | 200        | 200         | OK                 |

  Scenario Outline: Testing app returns failure if the given apache module is not loaded
    Given using OCS API version "<ocs-api-version>"
    When the administrator checks if "random_module_123" apache module is installed using the testing API
    Then the HTTP reason phrase should be "<http-reason-phrase>"
    And the HTTP status code should be "<http-status>"
    And the OCS status code should be "<ocs-status>"
    And the OCS status message should be "<ocs-message>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase | ocs-message                                             |
      | 1               | 998        | 200         | OK                 | random_module_123 could not be found in apache_modules. |
      | 2               | 404        | 404         | Not Found          | random_module_123 could not be found in apache_modules. |