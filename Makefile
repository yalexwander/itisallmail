tests_unit:
	IAM_TEST_ENV=default vendor/bin/phpunit -c tests/phpunit.xml

# tests_functional:

tests: tests_unit

lint_phpstan:
	vendor/bin/phpstan

lint_phpstan_emacs:
	vendor/bin/phpstan --error-format=raw


lint_phpcs:
	vendor/bin/phpcs lib/ scripts/

lint_phpcs_emacs:
	vendor/bin/phpcs --report=emacs lib/ scripts/
