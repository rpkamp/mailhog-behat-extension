# Mailhog Behat Extension ![Packagist Version](https://img.shields.io/packagist/v/rpkamp/mailhog-behat-extension)s

A simple PHP (7.2+) [Behat] extension for [Mailhog][mailhog].

## Installation

This package does not require any specific HTTP client implementation, but it requires [rpkamp/mailhog-client][mailhog-client], which is based on [HTTPlug][httplug], so you can inject your own HTTP client of choice. So you when you install this extension make sure you either already have an HTTP client installed, or install one at the same time as installing this extension, otherwise installation will fail.

```bash
composer require rpkamp/mailhog-behat-extension <your-http-client-of-choice>
```

For more information please refer to the [HTTPlug documentation for Library Users][httplug-docs].

## Usage

### Register extension in Behat

Add the extension to your `behat.yml` like so:

```yaml
default:
  suites:
    # your suite configuration here
  extensions:
    rpkamp\Behat\MailhogExtension:
      base_url: http://localhost:8025
      purge_tag: email # optional, defaults to 'email'
```

The `base_url` is the URL where the Mailhog Web UI is listening to (by default this is `http://localhost:8025).
The `purge_tag` is the behat tag that triggers a purge in mailhog before the scenario/feature (see "Use email tag to purge emails before scenarios")

### Use MailhogContext

The easiest way to get started is to configure behat to use `rpkamp\Behat\MailhogExtension\Context\MailhogContext` like so:

```yaml
default:
  suites:
    contexts:
      - rpkamp\Behat\MailhogExtension\Context\MailhogContext
```

This enables the following Gherkin for your scenarios to make assumptions on received email messages:

```gherkin
Given my inbox is empty
Then I should see an email with subject "subject"
Then I should see an email with body "body"
Then I should see an email from "sender@domain.example"
Then I should see an email with subject "subject" and body "body"
Then I should see an email with subject "subject" and body "body" from "sender@domain.example"
Then I should see an email with subject "subject" from "sender@domain.example"
Then I should see an email to "recipient@domain.example"
Then I should see an email with subject "subject" to "recipient@domain.example"
Then I should see an email with body "body" to "recipient@domain.example"
Then I should see an email from "sender@domain.example" to "recipient@domain.example"
Then I should see an email with subject "subject" and body "body" to "recipient@domain.example"
Then I should see an email with subject "subject" and body "body" from "sender@domain.example" to "recipient@domain.example"
Then I should see an email with subject "subject" from "sender@domain.example" to "recipient@domain.example"
Then I should see "some text" in email
Then there should be 2 emails in my inbox
Then I should see an email with attachment "lorem-ipsum.pdf"
```

- `Given my inbox is empty` will actually purge all emails from Mailhog.

- The `2` in `Then there should be 2 emails in my inbox` is variable, and the 's' in 'emails' is optional, so 'Then there is 1 email in my inbox' also works.

Alternatively you can "open" an email an run assumptions on the opened email:

```gherkin
When I open the latest email from "sender@domain.example"
When I open the latest email to "recipient@domain.example"
When I open the latest email with subject "Hello world"
When I open the latest email from "sender@domain.example" with subject "Hello world"
When I open the latest email to "recipient@domain.example" with subject "Hello world"
When I open the latest email with body "body"
When I open the latest email with subject "subject" and body "body"
When I open the latest email from "sender@domain.example" to "recipient@domain.example"
When I open the latest email from "sender@domain.example" with body "body"
When I open the latest email to "recipient@domain.example" with body "body"
When I open the latest email from "sender@domain.example" with subject "subject" and body "body"
When I open the latest email to "recipient@domain.example" with subject "subject" and body "body"
When I open the latest email from "sender@domain.example" to "recipient@domain.example" with subject "subject" and body "body"
Then I should see "Hello world" in the opened email
Then I should see an attachment with filename "lorem-ipsum.pdf" in the opened email
```

Take care that the implementation of this currently isn't very efficient. If there are a lot of emails in Mailhog it might take a while, especially when the email you're looking for is not there.

### Implement MailhogAwareContext

If you want to implement something more advanced than `rpkamp\Behat\MailhogExtension\Context\MailhogContext` offers you can also implement `rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext` in your own context and implement the method in that interface:

```php
<?php
declare(strict_types=1);

use rpkamp\Mailhog\MailhogClient;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;

class FeatureContext implements MailhogAwareContext
{
    private $mailhog;
    
    public function setMailhog(MailhogClient $client)
    {
         $this->mailhog = $client;
    }
}
```

Now every time your FeatureContext is initialized Behat will inject an `rpkamp\MailhogClient` in it you can use using the `$mailhog` property of your context. For example:

```php
<?php
declare(strict_types=1);

use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;

class FeatureContext implements MailhogAwareContext
{
    // implement setMailhog as above
    
    /**
     * @Then /^there should be (\d+) email(?:s) in my inbox$/
     */
    public function thereShouldBeEmailInMyInbox(int $numEmails)
    {
        if ($numEmails !== $this->mailhog->getNumberOfMessages()) {
            throw new Exception('Unexpected number of messages.');
        }
    }
}
```

### Use email tag to purge emails before scenarios

In scenarios where you want to make sure you have received the correct number of messages you will want to purge mailhog before the scenario is started. In order to do that add the `@email` tag to either the scenario or the feature. As usual, when you apply it to the feature it applies to all scenarios within that feature.

```gherkin
Feature:

    @email
    Scenario: I should receive no more than 1 email
      Given some state
      When something happened
      Then there should be 1 email in my inbox
```

If you want to use a different tag you can supply the name (without the initial @) in the `purge_tag` setting of this extension.

### Implement OpenedEmailStorageAwareContext (Advanced)

If you want to write an extension/context for behat that uses the feature of opening email from this extension you can have your `Context` implement `OpenedEmailStorageAwareContext`:

```php
<?php

use rpkamp\Behat\MailhogExtension\Context\OpenedEmailStorageAwareContext;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;

class FeatureContext implements OpenedEmailStorageAwareContext
{
    /**
     * @var OpenedEmailStorage
     */
    private $storage;

    public function setOpenedEmailStorage(OpenedEmailStorage $storage)
    {
        $this->openedEmailStorage = $storage;
    }

    /**
     * @Then ^I do something with the opened email$
     */
    public function iDoSomethingWithTheOpenedEmail(): void
    {
        if (!$this->storage->hasOpenedEmail()) {
            throw new RuntimeException('No email opened, unable to do something!');
        }

        /** @var \rpkamp\Mailhog\Message\Message $openedEmail */
        $openedEmail = $this->storage->getOpenedEmail();

        // do stuff with $openedEmail
    }
}
```

## Run tests

Make sure you have Mailhog running and run:

```bash
make test
```

### Running Mailhog for tests

You can either run your own instance of Mailhog or use the provided docker-compose file to run one for you.
To run Mailhog with Docker make sure you have Docker and docker-compose installed and run:

```bash
docker-compose up -d
```

### Mailhog ports for tests

To prevent port collisions with any other Mailhog instances while testing the tests expect Mailhog to listen to SMTP on port 3025 (instead of the default 1025) and to HTTP traffic on port 10025 (instead of the default 8025).

[behat]: http://behat.org/
[mailhog]: https://github.com/mailhog/MailHog
[httplug]: https://github.com/php-http/httplug
[mailhog-client]: https://github.com/rpkamp/mailhog-client
[httplug-docs]: http://docs.php-http.org/en/latest/httplug/users.html
