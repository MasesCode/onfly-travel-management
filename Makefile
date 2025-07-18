.PHONY: help setup setup-local up down logs test shell-backend shell-frontend clean

help: ## Mostra esta ajuda
	@echo "Onfly Travel Management - Comandos disponíveis:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Setup completo com Docker
	@./setup.sh

setup-local: ## Setup para desenvolvimento local
	@./setup-local.sh

up: ## Sobe os containers
	@docker-compose up -d

down: ## Para os containers
	@docker-compose down

logs: ## Mostra logs dos containers
	@docker-compose logs -f

test: ## Executa os testes
	@./run-tests.sh

shell-backend: ## Acessa o shell do backend
	@docker-compose exec backend bash

shell-frontend: ## Acessa o shell do frontend
	@docker-compose exec frontend sh

clean: ## Remove containers e volumes
	@docker-compose down -v
	@docker system prune -f

migrate: ## Executa migrations
	@docker-compose exec backend php artisan migrate

seed: ## Executa seeders
	@docker-compose exec backend php artisan db:seed

fresh: ## Recria o banco de dados
	@docker-compose exec backend php artisan migrate:fresh --seed

cache-clear: ## Limpa o cache
	@docker-compose exec backend php artisan cache:clear
	@docker-compose exec backend php artisan config:clear
	@docker-compose exec backend php artisan view:clear
