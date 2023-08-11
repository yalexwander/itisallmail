tests_unit:
	IAM_TEST_ENV=default vendor/bin/phpunit -c tests/phpunit_unit.xml

tests_func:
	IAM_TEST_ENV=func/fetch vendor/bin/phpunit -c tests/phpunit_func.xml

tests: tests_unit

lint_phpstan:
	vendor/bin/phpstan

lint_phpstan_emacs:
	vendor/bin/phpstan --error-format=raw


lint_phpcs:
	vendor/bin/phpcs lib/ scripts/

lint_phpcs_emacs:
	vendor/bin/phpcs --report=emacs lib/ scripts/

precommit_fix:
	vendor/bin/php-cs-fixer fix scripts/
	vendor/bin/php-cs-fixer fix	lib/ 
