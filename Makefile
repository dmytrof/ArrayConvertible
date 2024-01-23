PHP_COMMAND=php

.PHONY: lint
lint: phpcs-report psalm-no-cache

.PHONY: psalm-no-cache
psalm-no-cache:
	$(PHP_COMMAND) ./vendor/bin/psalm --show-info=false --no-cache

.PHONY: phpcs-report
phpcs-report:
	$(PHP_COMMAND) ./vendor/squizlabs/php_codesniffer/bin/phpcs -p

.PHONY: phpcs-fix
phpcs-fix: phpcs-report
	$(PHP_COMMAND) ./vendor/squizlabs/php_codesniffer/bin/phpcbf -p

.PHONY: unit-tests
unit-tests: lint unit-tests-only

.PHONY: unit-tests-only
unit-tests-only:
	 $(PHP_COMMAND) ./vendor/phpunit/phpunit/phpunit
