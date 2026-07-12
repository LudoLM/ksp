# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc test

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) --env-file .env.local up --detach

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

start: build up ## Build and start the containers

restart: down start ## Restart the docker hub

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit $(c)

coverage : ## Start tests with phpunit and generate a code coverage report, pass the parameter "c=" to add options to phpunit, example: make coverage c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-text $(c)

phpstan: ## Run PHPStan
	@$(DOCKER_COMP) exec php vendor/bin/phpstan analyse

cs: ## Run PHP CS Fixer
	@$(DOCKER_COMP) exec php vendor/bin/php-cs-fixer fix

rector: ## Run Rector
	@$(DOCKER_COMP) exec php vendor/bin/rector process

messengerConsume: ## Show live logs from messenger-worker
	@$(DOCKER_COMP) logs -f messenger-worker

schedulerConsume: ## Show live logs from scheduler-worker
	@$(DOCKER_COMP) logs -f scheduler-worker

logs-scheduler: ## Show live logs from scheduler-worker
	@$(DOCKER_COMP) logs -f scheduler-worker

scheduler-debug: ## Display scheduled tasks debug info
	@$(PHP) bin/console debug:scheduler

analyse: cs rector phpstan test ## Run PHPStan, PHP CS Fixer and Rector

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf
