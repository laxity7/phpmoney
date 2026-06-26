DOCKER_PHP = docker run --rm -v "$(PWD)":/app -w /app php:8.1-cli
DOCKER_COMPOSER = docker run --rm -v "$(PWD)":/app -w /app composer:2

.PHONY: install test phpstan check bench

## Install composer dependencies
install:
	$(DOCKER_COMPOSER) composer install --no-interaction --prefer-dist

## Run PHPUnit tests
test:
	$(DOCKER_PHP) vendor/bin/phpunit

## Run PHPStan static analysis
phpstan:
	$(DOCKER_PHP) vendor/bin/phpstan analyse --no-progress

## Run benchmark comparison (laxity7 vs symfony)
bench:
	$(DOCKER_PHP) php tests/Benchmark/bench.php

## Run all checks (tests + phpstan)
check: test phpstan
