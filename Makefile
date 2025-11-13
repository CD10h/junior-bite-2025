run_compose := docker-compose -f docker-compose.yml 
php_exec := $(run_compose) exec php 

ARGS = $(filter-out $@,$(MAKECMDGOALS))


up:
	$(run_compose) up -d

down:
	$(run_compose) down

watch-logs:
	$(run_compose) logs -f

composer:
	$(php_exec) composer $(ARGS)

console:
	$(php_exec) bin/console $(ARGS)

new-migration:
	$(MAKE) console ARGS=make:migration

migrate-all:
	$(MAKE) console ARGS=doctrine:migrations:migrate

test-all:
	$(php_exec) bin/phpunit tests

restart: down up

