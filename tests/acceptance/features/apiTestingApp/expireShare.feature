@api @skipOnOcV10.6 @skipOnOcV10.7
Feature: expire a share

  Scenario: set a share to expire yesterday and verify that it is not accessible
    Given these users have been created with default attributes and without skeleton files:
      | username |
      | Alice    |
      | Brian    |
    And user "Alice" has uploaded file "filesForUpload/textfile.txt" to "/textfile0.txt"
    And user "Alice" has created a share with settings
      | path        | /textfile0.txt |
      | shareType   | user           |
      | shareWith   | Brian          |
      | permissions | read,share     |
      | expireDate  | +15 days       |
    When the administrator expires the last created share using the testing API
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And the fields of the last response should include
      | original_date | +15 days  |
      | new_date      | yesterday |
    And user "Alice" should not see the share id of the last share
    And user "Brian" should not see the share id of the last share
    And as "Brian" file "/textfile0.txt" should not exist
    And as "Alice" file "/textfile0.txt" should exist
