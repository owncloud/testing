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
    Then the file "data/loremfile.txt" should not exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can create directory in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates the directory "data/lorem-dir" in server root using the testing API
    And the administrator creates the file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" using the testing API
    Then the file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can delete directory in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates the directory "data/lorem-dir" in server root using the testing API
    And the administrator creates the file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" using the testing API
    And the administrator deletes the directory "data/lorem-dir" using the testing API
    Then the file "data/lorem-dir/loremfile.txt" should not exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |