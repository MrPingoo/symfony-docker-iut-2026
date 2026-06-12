# =============================================================================
# Raccourcis pour piloter le projet. Tape `make` pour voir la liste.
# =============================================================================
.DEFAULT_GOAL := help
DC = docker compose
PHP = $(DC) exec php

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

build: ## Construit les images Docker
	$(DC) build

up: ## Démarre les conteneurs en arrière-plan
	$(DC) up -d

down: ## Arrête et supprime les conteneurs
	$(DC) down

logs: ## Affiche les logs en continu
	$(DC) logs -f

sh: ## Ouvre un shell dans le conteneur PHP
	$(PHP) sh

install: ## Installe les dépendances Composer
	$(PHP) composer install

# ----- Base de données -------------------------------------------------------
db-create: ## Crée la base de données
	$(PHP) php bin/console doctrine:database:create --if-not-exists

migrate: ## Joue les migrations
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

diff: ## Génère une migration à partir des entités
	$(PHP) php bin/console doctrine:migrations:diff

fixtures: ## Charge les données de démo
	$(PHP) php bin/console doctrine:fixtures:load --no-interaction

# ----- Setup complet ---------------------------------------------------------
setup: build up install db-create migrate fixtures jwt-keys ## Installe TOUT le projet d'un coup
	@echo "\n✅  API prête sur http://localhost:8080  —  Adminer sur http://localhost:8081\n"

jwt-keys: ## Génère les clés JWT (auth)
	$(PHP) php bin/console lexik:jwt:generate-keypair --skip-if-exists

cc: ## Vide le cache Symfony
	$(PHP) php bin/console cache:clear

test: ## Lance les tests (crée/migre la base de test au passage)
	$(PHP) php bin/console doctrine:database:create --env=test --if-not-exists
	$(PHP) php bin/console doctrine:migrations:migrate --env=test --no-interaction
	$(PHP) php bin/console doctrine:fixtures:load --env=test --no-interaction
	$(PHP) php vendor/bin/phpunit
