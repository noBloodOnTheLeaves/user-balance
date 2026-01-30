SHELL := /bin/bash

.PHONY: help up down restart build logs ps app node db vite \
	composer npm artisan migrate seed test shell

help:
	@echo "Usage: make <target>"
	@echo ""
	@echo "Docker:"
	@echo "  up       - build and start containers"
	@echo "  down     - stop and remove containers"
	@echo "  restart  - restart containers"
	@echo "  build    - build images"
	@echo "  logs     - tail logs"
	@echo "  ps       - show container status"
	@echo ""
	@echo "App:"
	@echo "  composer - run composer install"
	@echo "  npm      - run npm install"
	@echo "  vite     - run npm dev"
	@echo "  artisan  - run artisan command (make artisan ARGS='...')"
	@echo "  migrate  - run migrations"
	@echo "  seed     - run db seeders"
	@echo "  test     - run phpunit tests"
	@echo "  key      - key gen"
	@echo "  worker   - start worker"

up:
	docker compose up --build -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build

logs:
	docker compose logs -f

ps:
	docker compose ps

app:
	docker compose exec app bash

node:
	docker compose exec node sh

db:
	docker compose exec db psql -U laravel -d laravel

composer:
	docker compose exec app composer install

npm:
	docker compose exec node npm install

vite:
	docker compose exec node npm run dev -- --host

artisan:
	docker compose exec app php artisan $(ARGS)

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test

shell:
	docker compose exec app bash

key:
	docker compose exec app php artisan key:generate

worker:
	docker compose exec app php artisan queue:work
