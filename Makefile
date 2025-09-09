# Makefile for Laravel GCS Storage Docker Environment

# Variables
COMPOSE = docker compose
APP_SERVICE = app
DB_SERVICE = mysql

.PHONY: help build up down restart logs shell test install setup clean

# Default target
help: ## Show this help message
	@echo "Laravel GCS Storage - Docker Commands"
	@echo "====================================="
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	$(COMPOSE) build

up: ## Start all services
	$(COMPOSE) up -d

down: ## Stop all services
	$(COMPOSE) down

restart: ## Restart all services
	$(COMPOSE) restart

logs: ## View logs for all services
	$(COMPOSE) logs -f

logs-app: ## View logs for app service only
	$(COMPOSE) logs -f $(APP_SERVICE)

shell: ## Access the application container shell
	$(COMPOSE) exec $(APP_SERVICE) bash

mysql: ## Access MySQL CLI
	$(COMPOSE) exec $(DB_SERVICE) mysql -u laravel -p laravel_gcs_storage

install: ## Install composer dependencies
	$(COMPOSE) exec $(APP_SERVICE) composer install

test: ## Run PHPUnit tests
	$(COMPOSE) exec $(APP_SERVICE) php artisan test

migrate: ## Run database migrations
	$(COMPOSE) exec $(APP_SERVICE) php artisan migrate

migrate-fresh: ## Fresh migrate with seeding
	$(COMPOSE) exec $(APP_SERVICE) php artisan migrate:fresh --seed

key-generate: ## Generate application key
	$(COMPOSE) exec $(APP_SERVICE) php artisan key:generate

cache-clear: ## Clear all Laravel caches
	$(COMPOSE) exec $(APP_SERVICE) php artisan cache:clear
	$(COMPOSE) exec $(APP_SERVICE) php artisan config:clear
	$(COMPOSE) exec $(APP_SERVICE) php artisan route:clear

setup: ## Complete setup (first time)
	@echo "🐳 Setting up Laravel GCS Storage..."
	$(COMPOSE) build
	$(COMPOSE) up -d
	@echo "⏳ Waiting for services to be ready..."
	sleep 30
	$(COMPOSE) exec $(APP_SERVICE) composer install
	$(COMPOSE) exec $(APP_SERVICE) php artisan key:generate
	$(COMPOSE) exec $(APP_SERVICE) php artisan migrate:fresh --seed
	$(COMPOSE) exec $(APP_SERVICE) php artisan storage:link
	$(COMPOSE) exec $(APP_SERVICE) chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
	$(COMPOSE) exec $(APP_SERVICE) chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
	@echo "🎉 Setup complete!"

clean: ## Remove all containers, volumes, and images
	$(COMPOSE) down -v --rmi all --remove-orphans

status: ## Show status of all services
	$(COMPOSE) ps

backup-db: ## Create database backup
	$(COMPOSE) exec $(DB_SERVICE) mysqldump -u laravel -p laravel_gcs_storage > backup_$$(date +%Y%m%d_%H%M%S).sql

restore-db: ## Restore database from backup (specify FILE=backup.sql)
	$(COMPOSE) exec -T $(DB_SERVICE) mysql -u laravel -p laravel_gcs_storage < $(FILE)

artisan: ## Run artisan command (specify CMD="migrate:status")
	$(COMPOSE) exec $(APP_SERVICE) php artisan $(CMD)

composer: ## Run composer command (specify CMD="require package/name")
	$(COMPOSE) exec $(APP_SERVICE) composer $(CMD)