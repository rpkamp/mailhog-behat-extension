Feature: As the developer of this context I want it to function correctly

  Scenario: As a user I want to receive an email
    Given I send an email with subject "Hello" and body "How are you?"
    Then I should receive an email with subject "Hello" and body "How are you?"

  @email
  Scenario: As a developer I want the extension to purge the inbox for email tag
    Given I send an email with subject "Hello" and body "How are you?"
    Then there should be 1 email in my inbox
