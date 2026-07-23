SHELL := /bin/sh
APP_CONTAINER := app-library-api
APP_DIR := /workspace/app-library-api

.PHONY: help up down restart ps logs logs-app logs-vite migrate fresh seed shell app-shell vite-shell composer-install npm-install assets-fix clear-hot aliases alias-artisan alias-php

help:
	@echo "Targets:"
	@echo "  make up              - start containers"
	@echo "  make down            - stop containers"
	@echo "  make restart         - restart containers"
	@echo "  make ps              - show container status"
	@echo "  make logs            - follow all compose logs"
	@echo "  make logs-app        - follow Laravel app logs"
	@echo "  make logs-vite       - follow Vite logs"
	@echo "  make migrate         - run Laravel migrations"
	@echo "  make fresh           - fresh migrations (drops all tables)"
	@echo "  make seed            - run Laravel seeders"
	@echo "  make shell           - shell into app container"
	@echo "  make composer-install- run composer install in app container"
	@echo "  make npm-install     - run npm install in vite container"
	@echo "  make aliases         - print shell wrappers for artisan, php (aka ph)"
	@echo "  make clear-hot       - remove stale Vite hot file if present"
	@echo "  make assets-fix      - clear stale Vite hot file and rebuild assets"

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d

ps:
	docker compose ps

logs:
	docker compose logs -f

logs-app:
	docker compose logs -f app

logs-vite:
	docker compose logs -f vite

migrate:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && php artisan migrate --force"

fresh:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && php artisan migrate:fresh --force"

seed:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && php artisan db:seed --force"

fresh-seed:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && php artisan migrate:fresh --seed --force"

shell:
	docker exec -it $(APP_CONTAINER) sh

app-shell: shell

vite-shell:
	docker exec -it app-library-api-vite sh

composer-install:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && composer install"

composer-update:
	docker exec -it $(APP_CONTAINER) sh -lc "cd $(APP_DIR) && composer update"

npm-install:
	docker exec -it app-library-api-vite sh -lc "cd $(APP_DIR) && npm install"

npm-update:
	docker exec -it app-library-api-vite sh -lc "cd $(APP_DIR) && npm update"

aliases:
	@printf '%s\n' 'artisan() { docker exec -it -w $(APP_DIR) $(APP_CONTAINER) php artisan "$$@"; }'
	@printf '%s\n' 'php() { docker exec -it -w $(APP_DIR) $(APP_CONTAINER) php "$$@"; }'
	@printf '%s\n' 'tail() { docker exec -it -w $(APP_DIR) $(APP_CONTAINER) tail "$$@"; }'

	eval "$(make aliases)" && echo "Aliases loaded: artisan, php, tail"
	$(echo "You can now run artisan commands like: artisan migrate" && echo "You can now run php commands like: php -v" && echo "You can now run tail commands like: tail -n 100 storage/logs/laravel.log"	)

clear-hot:
	@if [ -f public/hot ]; then \
		rm -f public/hot; \
		echo "Removed stale public/hot"; \
	else \
		echo "No public/hot found"; \
	fi

assets-fix: clear-hot
	docker exec -it app-library-api-vite sh -lc "cd $(APP_DIR) && npm run build"
