# List available commands
help:
	@awk '/^#/{c=substr($$0,3);next}c&&/^[[:alpha:]][[:alnum:]_-]+:/{print substr($$1,1,index($$1,":")),c}1{c=0}' $(MAKEFILE_LIST) | column -s: -t

# Run all tests
test: lint code-style unit-tests behat phpstan

# Lint all php files
lint:
	vendor/bin/parallel-lint --exclude vendor/ .

# Check code for style problems
code-style: phpmd phpcs

# Check code for design problems
phpmd:
	php phpmd.phar src/ text phpmd.xml --suffixes php

# Check code adheres to PSR-2
phpcs:
	vendor/bin/phpcs --runtime-set ignore_warnings_on_exit 1 --standard=ruleset.xml src/

# Run unit tests
unit-tests:
	vendor/bin/phpunit --testdox -v

# Run behat
behat:
	vendor/bin/behat

phpstan:
	vendor/bin/phpstan analyze --level max src/ tests/

.PHONY: help test lint code-style phpmd phpcs unit-tests behat phpstan
