Feature: As the developer of this context I want it to function correctly

  Scenario: As a user I want to receive an email
    Given I send an email with subject "Hello" and body "How are you?"
    Then I should receive an email with subject "Hello" and body "How are you?"
