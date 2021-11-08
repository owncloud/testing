@api
Feature: Test ServerFiles feature of testing app

  Scenario Outline: Testing app can create file in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates file "data/loremfile.txt" with content "lorem ipsum" using the testing API
    Then the file "data/loremfile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can delete file in server root
    Given using OCS API version "<ocs-api-version>"
    And the administrator has created file "data/loremfile.txt" with content "lorem ipsum"
    When the administrator deletes file "data/loremfile.txt" using the testing API
    Then the file "data/loremfile.txt" should not exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can create directory in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates directory "data/lorem-dir" in server root using the testing API
    And the administrator creates file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" using the testing API
    Then the file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can delete directory in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates directory "data/lorem-dir" in server root using the testing API
    And the administrator creates file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" using the testing API
    And the administrator deletes directory "data/lorem-dir" using the testing API
    Then the file "data/lorem-dir/loremfile.txt" should not exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can move a directory in server root
    Given using OCS API version "<ocs-api-version>"
    When the administrator creates directory "data/lorem-dir" in server root using the testing API
    And the administrator creates file "data/lorem-dir/loremfile.txt" with content "lorem ipsum" using the testing API
    And the administrator moves directory "data/lorem-dir" to "data/new-lorem-dir" using the testing API
    Then the file "data/lorem-dir" should not exist in the server root
    And the file "data/new-lorem-dir/loremfile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |

  Scenario Outline: Testing app can rename a file in server root
    Given using OCS API version "<ocs-api-version>"
    And the administrator creates file "loremfile.txt" with content "lorem ipsum" using the testing API
    And the administrator moves file "loremfile.txt" to "newLoremFile.txt" using the testing API
    Then the file "loremfile.txt" should not exist in the server root
    And the file "newLoremFile.txt" with content "lorem ipsum" should exist in the server root
    Examples:
      | ocs-api-version |
      | 1               |
      | 2               |
