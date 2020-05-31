@api @federation-app-required
Feature: Test trusted server feature of testing app

  Scenario Outline: Add new trusted server using the testing api
    Given using OCS API version "<ocs-api-version>"
    When the administrator adds url "http://new-oc.com" as trusted server using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    And url "http://new-oc.com" should be a trusted server
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 201        | 201         | Created            |
      | 2               | 201        | 201         | Created            |

  Scenario Outline: Add multiple trusted servers using the testing api
    Given using OCS API version "<ocs-api-version>"
    When the administrator adds url "http://new-oc.com" as trusted server using the testing API
    When the administrator adds url "http://new-oc1.com" as trusted server using the testing API
    When the administrator adds url "http://aafnobadal.com" as trusted server using the testing API
    Then the trusted server list should include these urls:
      | url                   |
      | http://new-oc.com     |
      | http://new-oc1.com    |
      | http://aafnobadal.com |
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Delete trusted servers using the testing api
    Given using OCS API version "<ocs-api-version>"
    And the administrator has added url "http://new-oc.com" as trusted server
    When the administrator deletes url "http://new-oc.com" from trusted servers using the testing API
    Then the HTTP status code should be "<http-status>"
    And url "http://new-oc.com" should not be a trusted server
    Examples:
      | ocs-api-version | http-status |
      | 1               | 204         |
      | 2               | 204         |

  Scenario Outline: Delete all trusted servers using the testing api
    Given using OCS API version "<ocs-api-version>"
    And the administrator has added url "http://new-oc.com" as trusted server
    And the administrator has added url "http://new-oc1.com" as trusted server
    And the administrator has added url "http://aafnobadal.com" as trusted server
    When the administrator deletes all trusted servers using the testing API
    Then the HTTP status code should be "<http-status>"
    And the trusted server list should be empty
    Examples:
      | ocs-api-version | http-status |
      | 1               | 204         |
      | 2               | 204         |
