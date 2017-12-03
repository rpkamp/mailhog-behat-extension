# Mailhog Behat Extension [![Build Status](https://travis-ci.org/rpkamp/mailhog-behat-extensio.svg?branch=master)](https://travis-ci.org/rpkamp/mailhog-behat-extension)

A simple PHP (7.1+) [Behat] extension for [Mailhog][mailhog].

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
```

The `base_url` is the URL where the Mailhog Web UI is listening to (by default this is `http://localhost:8025).

### Implement MailhogAwareContext

Let your FeatureContext implement `MailhogAwareContext` and implement the method in that interface:

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

Now every time your FeatureContext is initialized Behat will inject a MailhogClient in it you can use using the `$mailhog` property of your context. For example:

```php
<?php
declare(strict_types=1);

use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;

class FeatureContext implements MailhogAwareContext
{
    // implement setMailhog as above
    
    /**
     * @Then /^there should be (\d+) email in my inbox$/
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
