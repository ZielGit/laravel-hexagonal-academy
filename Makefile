# ============================================
# Laravel Hexagonal Academy - Docker Makefile
# ============================================

.PHONY: help

help: ## Show this help message
	@echo "Laravel Hexagonal Academy - Docker Commands"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ============================================
# Setup Commands
# ============================================

setup: ## Initial project setup
	@echo "🚀 Setting up Laravel Hexagonal Academy..."
	cp .env.example .env
	docker compose build --no-cache
	docker compose up -d
	@echo "⏳ Waiting for services to be ready..."
	sleep 10
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	docker compose exec app php artisan db:seed
	@echo "✅ Setup complete!"
	@echo "🌐 App: http://localhost:8000"
	@echo "📧 Mailhog: http://localhost:8025"

install: setup ## Alias for setup

# ============================================
# Container Management
# ============================================

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

rebuild: ## Rebuild all containers
	docker compose down
	docker compose build --no-cache
	docker compose up -d

ps: ## Show running containers
	docker compose ps

logs: ## Show logs from all containers
	docker compose logs -f

logs-app: ## Show logs from app container
	docker compose logs -f app

logs-nginx: ## Show logs from nginx container
	docker compose logs -f nginx

logs-postgres: ## Show logs from postgres container
	docker compose logs -f postgres

# ============================================
# Application Commands
# ============================================

bash: ## Access app container bash
	docker compose exec app sh

artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	docker compose exec app php artisan $(cmd)

composer: ## Run composer command (usage: make composer cmd="install")
	docker compose exec app composer $(cmd)

fresh: ## Fresh database with seeders
	docker compose exec app php artisan migrate:fresh --seed

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

seed: ## Run database seeders
	docker compose exec app php artisan db:seed

rollback: ## Rollback last migration
	docker compose exec app php artisan migrate:rollback

cache-clear: ## Clear all caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

optimize: ## Optimize application (production)
	docker compose exec app php artisan optimize
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache

# ============================================
# Testing Commands
# ============================================

test: ## Run all tests
	docker compose exec app php artisan test

test-unit: ## Run unit tests
	docker compose exec app php artisan test --testsuite=Unit

test-integration: ## Run integration tests
	docker compose exec app php artisan test --testsuite=Integration

test-feature: ## Run feature tests
	docker compose exec app php artisan test --testsuite=Feature

test-coverage: ## Run tests with coverage
	docker compose exec app php artisan test --coverage

stan: ## Run PHPStan static analysis
	docker compose exec app vendor/bin/phpstan analyse

pint: ## Run Laravel Pint (code style fixer)
	docker compose exec app vendor/bin/pint

pint-test: ## Test code style without fixing
	docker compose exec app vendor/bin/pint --test

quality: stan pint-test test ## Run all quality checks

# ============================================
# Database Commands
# ============================================

db-console: ## Access PostgreSQL console
	docker compose exec postgres psql -U postgres -d laravel_hexagonal_academy

db-dump: ## Dump database to file
	docker compose exec postgres pg_dump -U postgres laravel_hexagonal_academy > backup_$$(date +%Y%m%d_%H%M%S).sql

db-restore: ## Restore database from file (usage: make db-restore file="backup.sql")
	docker compose exec -T postgres psql -U postgres laravel_hexagonal_academy < $(file)

redis-cli: ## Access Redis CLI
	docker compose exec redis redis-cli

# ============================================
# Maintenance Commands
# ============================================

clean: ## Clean all Docker resources
	docker compose down -v
	docker system prune -af

permissions: ## Fix file permissions
	docker compose exec app chown -R appuser:appgroup /var/www
	docker compose exec app chmod -R 755 /var/www/storage
	docker compose exec app chmod -R 755 /var/www/bootstrap/cache

# ============================================
# Development Tools
# ============================================

tinker: ## Open Laravel Tinker
	docker compose exec app php artisan tinker

queue-work: ## Start queue worker
	docker compose exec app php artisan queue:work

queue-restart: ## Restart queue workers
	docker compose exec app php artisan queue:restart

horizon: ## Start Laravel Horizon (if installed)
	docker compose exec app php artisan horizon

watch-logs: ## Watch application logs
	docker compose exec app tail -f storage/logs/laravel.log

# ============================================
# Production Commands
# ============================================

prod-deploy: ## Deploy to production
	git pull
	docker compose -f docker-compose.prod.yml down
	docker compose -f docker-compose.prod.yml build --no-cache
	docker compose -f docker-compose.prod.yml up -d
	docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
	docker compose -f docker-compose.prod.yml exec app php artisan optimize

# ============================================
# Helper Information
# ============================================

status: ## Show project status
	@echo "📊 Project Status:"
	@echo ""
	@docker compose ps
	@echo ""
	@echo "🌐 URLs:"
	@echo "   App:      http://localhost:8000"
	@echo "   API:      http://localhost:8000/api/v1"
	@echo "   Mailhog:  http://localhost:8025"
	@echo "   pgAdmin:  http://localhost:5050"
	@echo ""
	@echo "🗄️  Database:"
	@echo "   Host:     localhost"
	@echo "   Port:     5432"
	@echo "   Database: laravel_hexagonal_academy"
	@echo "   User:     postgres"
	@echo ""
