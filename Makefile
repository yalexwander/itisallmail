tests_unit:
	IAM_TEST_ENV=default vendor/bin/phpunit -c tests/phpunit.xml

# tests_functional:

tests: tests_unit
