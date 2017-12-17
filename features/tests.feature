Feature: As the developer of this context I want it to function correctly

  Scenario: As a user I want to receive an email
    Given I send an email with subject "Hello" and body "How are you?"
    Then I should see an email with subject "Hello"
    And I should see an email with body "How are you?"
    And I should see an email from "me@myself.example"
    And I should see an email from "Myself"
    And I should see an email with subject "Hello" and body "How are you?"
    And I should see an email with subject "Hello" and body "How are you?" from "me@myself.example"
    And I should see an email with subject "Hello" from "me@myself.example"
    And I should see "How" in email

  @email
  Scenario: As a developer I want the extension to purge the inbox for email tag
    Given I send an email with subject "Hello" and body "How are you?"
    Then there should be 1 email in my inbox

  Scenario: As a developer I want the extension to purge the inbox when I say so
    Given my inbox is empty
    Then there should be 0 emails in my inbox

  Scenario: As a developer I want the extension to see attachments in emails
    Given I send an email with attachment "hello.txt"
    Then I should receive an email with attachment "hello.txt"
