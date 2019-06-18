Feature: As the developer of this context I want it to function correctly

  Scenario: As a user I want to receive an email
    Given I send an email with subject "Hello" and body "How are you?" to "test@example.org"
    Then I should see an email with subject "Hello"
    And I should see an email with body "How are you?"
    And I should see an email from "me@myself.example"
    And I should see an email from "Myself"
    And I should see an email with subject "Hello" and body "How are you?"
    And I should see an email with subject "Hello" and body "How are you?" from "me@myself.example"
    And I should see an email with subject "Hello" from "me@myself.example"
    And I should see "How" in email
    And I should see an email to "test@example.org"
    And I should see an email with subject "Hello" to "test@example.org"
    And I should see an email with body "How are you?" to "test@example.org"
    And I should see an email from "me@myself.example" to "test@example.org"
    And I should see an email from "Myself" to "test@example.org"
    And I should see an email with subject "Hello" and body "How are you?" to "test@example.org"
    And I should see an email with subject "Hello" and body "How are you?" from "me@myself.example" to "test@example.org"
    And I should see an email with subject "Hello" from "me@myself.example" to "test@example.org"

  Scenario: As a user I want to open an email based on subject
    Given I send an email with subject "Hello" and body "How are you?" to "test@example.org"
    Given I send an email with subject "Hello" and body "I'm fine" to "test@example.org"
    When I open the latest email with subject "Hello"
    Then I should see "I'm fine" in the opened email

  Scenario: As a user I want to open an email based on sender
    Given I send an email with subject "Hello" and body "How are you?" to "test@example.org"
    Given I send an email with subject "Goodbye" and body "See you later" to "test@example.org"
    When I open the latest email from "me@myself.example"
    Then I should see "See you later" in the opened email

  Scenario: As a user I want to open an email based on recipient
    Given I send an email with subject "Hello" and body "How are you?" to "test@example.org"
    Given I send an email with subject "Goodbye" and body "See you later" to "test@example.org"
    When I open the latest email to "test@example.org"
    Then I should see "See you later" in the opened email

  Scenario: As a user I want to check for an attachment in an opened email
    Given I send an email with attachment "hello.txt"
    When I open the latest email from "me@myself.example"
    Then I should see an attachment with filename "hello.txt" in the opened email

  @email
  Scenario: As a developer I want the extension to purge the inbox for email tag
    Given I send an email with subject "Hello" and body "How are you?"
    Then there should be 1 email in my inbox

  Scenario: As a developer I want the extension to purge the inbox when I say so
    Given my inbox is empty
    Then there should be 0 emails in my inbox

  Scenario: As a developer I want the extension to see attachments in emails
    Given I send an email with attachment "hello.txt"
    Then I should see an email with attachment "hello.txt"
