# Mailhog Behat Extension [![Build Status](https://travis-ci.org/rpkamp/mailhog-behat-extensio.svg?branch=master)](https://travis-ci.org/rpkamp/mailhog-behat-extension)

A simple PHP (7.1+) [Behat] extension for [Mailhog][mailhog].

## Installation

This package does not require any specific HTTP client implementation, but it requires [rpkamp/mailhog-client][mailhog-client], which is based on [HTTPlug][httplug], so you can inject your own HTTP client of choice. So you when you install this extension make sure you either already have an HTTP client installed, or install one at the same time as installing this extension, otherwise installation will fail.

```bash
composer require rpkamp/mailhog-behat-extension <your-http-client-of-choice>
```

For more information please refer to the [HTTPlug documentation for Library Users][httplug-docs].

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
