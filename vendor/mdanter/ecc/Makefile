test: phpunit phpcs

.PHONY: test phpunit phpcs

pretest:
		if [ ! -d vendor ] || [ ! -f composer.lock ]; then composer install; else echo "Already have dependencies"; fi

phpunit: pretest
		mkdir -p tests/output
		vendor/bin/phpunit --coverage-text --coverage-clover=tests/output/coverage.clover --coverage-html=tests/output/Results

test-examples:
		./validate_examples.sh

phpunit-ci: pretest
		vendor/bin/phpunit --coverage-text --coverage-clover=tests/output/coverage.clover

phpunit-full-ci: pretest
		vendor/bin/phpunit -c phpunit.full.xml --coverage-text --coverage-clover=tests/output/coverage.clover

ifndef STRICT
STRICT = 0
endif

ifeq "$(STRICT)" "1"
phpcs: pretest
		vendor/bin/phpcs --standard=PSR2 src tests/unit/
else
phpcs: pretest
		vendor/bin/phpcs --standard=PSR2 -n src tests/unit/
endif

phpcbf: pretest
		vendor/bin/phpcbf --standard=PSR2 -n src tests/unit/

ocular:
		wget https://scrutinizer-ci.com/ocular.phar

ifdef OCULAR_TOKEN
scrutinizer: ocular
		@php ocular.phar code-coverage:upload --format=php-clover tests/output/coverage.clover --access-token=$(OCULAR_TOKEN);
else
scrutinizer: ocular
		php ocular.phar code-coverage:upload --format=php-clover tests/output/coverage.clover;
endif

clean: clean-env clean-deps

clean-env:
		rm -rf coverage.clover
		rm -rf ocular.phar
		rm -rf tests/output/

clean-deps:
		rm -rf vendor/
