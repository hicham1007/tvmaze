DEV = docker compose -f docker-compose.dev.yml

.PHONY: dev down logs check check-backend check-frontend format sh-backend sh-frontend prod

dev: ## Start the development environment (http://localhost:5173)
	$(DEV) up --build

down: ## Stop the development environment
	$(DEV) down

logs:
	$(DEV) logs -f

check: check-backend check-frontend ## Linters, type checks and tests for both projects

check-backend:
	$(DEV) exec backend composer check

check-frontend:
	$(DEV) exec frontend pnpm check

format: ## Fix code style in both projects
	$(DEV) exec backend composer format
	$(DEV) exec frontend pnpm format

sh-backend:
	$(DEV) exec backend sh

sh-frontend:
	$(DEV) exec frontend sh

prod: ## Run the production images (http://localhost:8080)
	docker compose up --build
