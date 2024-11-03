.DEFAULT_GOAL=help
PLATFORM := $(shell uname)

include $(PWD)/.env
export

DC=docker-compose
APP=$(DC) exec --user=application app
APP_TTY=$(DC) exec --user=application -T app
DB=$(DC) exec db

help:  ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' Makefile \
	  | sort \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[0;32m%-30s\033[0m %s\n", $$1, $$2}'

up: down ## Run the docker container
	$(DC) up -d

build: ## Build the container
	$(DC) build --pull

down: ## Stop the docker containers and remove them
	$(DC) down

purge: ## Purge project
	$(DC) down --remove-orphans --volumes

clean: ## Stop all docker containers and clean the volumes
	$(DC) down --volumes

bash: ## Connect to the application console
	$(APP) /bin/bash

root: ## Connect to the application as root
	$(DC) exec app /bin/bash

db: ## Connect to the database console
	$(DB) /bin/bash

init-test-database:	## Init test database
	$(DB) psql -U ${DB_USERNAME} -d postgres -c "drop database if exists app_test;"
	$(DB) psql -U ${DB_USERNAME} -d postgres -c "create database app_test;"

test: ## Run the tests
	$(APP_TTY) composer test

cs-fix-dry: ## Dry-run php-cs-fixer
	$(APP_TTY) php vendor/bin/pint --config pint.json --test

cs-fix: ## Run php-cs-fixer
	$(APP_TTY) php vendor/bin/pint --config pint.json --cache-file=.pint.cache

composer: ## Run composer install
	$(APP_TTY) composer install
