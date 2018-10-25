@api
Feature: Test ServerFiles feature of testing app

  Scenario Outline: Testing app can create file in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates the file "data/loremfile.txt" with content "lorem ipsum" using the testing API
    Then the file "data/loremfile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can delete file in server root
    Given using OCS API version "<ocs-api-version>"
    And the administrator has created the file "data/loremfile.txt" with content "lorem ipsum"
    When the administrator deletes the file "data/loremfile.txt" using the testing API
    And the administrator reads the contents of the file "data/loremfile.txt" using the testing API
    Then the HTTP status code should be "<http-status>"
    And the HTTP reason phrase should be "<http-reason-phrase>"
    And the OCS status code should be "<ocs-status>"
    Examples:
      | ocs-api-version | ocs-status | http-status | http-reason-phrase |
      | 1               | 404        | 200         | OK                 |
      | 2               | 404        | 404         | Not Found          |