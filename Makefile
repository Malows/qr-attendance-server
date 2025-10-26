.PHONY: help build up down restart logs shell migrate fresh seed install

help: ## Mostrar este mensaje de ayuda
	@echo "Comandos disponibles:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Construir los contenedores Docker
	docker-compose build

up: ## Iniciar los contenedores
	docker-compose up -d

down: ## Detener los contenedores
	docker-compose down

restart: down up ## Reiniciar los contenedores

logs: ## Mostrar logs de todos los contenedores
	docker-compose logs -f

logs-app: ## Mostrar logs del contenedor de la aplicación
	docker-compose logs -f app

logs-nginx: ## Mostrar logs del contenedor Nginx
	docker-compose logs -f nginx

logs-db: ## Mostrar logs del contenedor de base de datos
	docker-compose logs -f db

shell: ## Abrir shell en el contenedor de la aplicación
	docker-compose exec app bash

shell-db: ## Abrir shell en el contenedor de base de datos
	docker-compose exec db psql -U laravel -d laravel

migrate: ## Ejecutar migraciones
	docker-compose exec app php artisan migrate

migrate-fresh: ## Ejecutar migraciones desde cero (borra todos los datos)
	docker-compose exec app php artisan migrate:fresh

seed: ## Ejecutar seeders
	docker-compose exec app php artisan db:seed

fresh-seed: ## Migración fresca con seeders
	docker-compose exec app php artisan migrate:fresh --seed

install: ## Instalación inicial completa
	@echo "Construyendo contenedores..."
	docker-compose build
	@echo "Iniciando contenedores..."
	docker-compose up -d
	@echo "Esperando que la base de datos esté lista..."
	sleep 10
	@echo "Ejecutando migraciones..."
	docker-compose exec app php artisan migrate
	@echo "Generando clave de aplicación..."
	docker-compose exec app php artisan key:generate
	@echo "Instalando Laravel Passport..."
	docker-compose exec app php artisan passport:install
	@echo "¡Instalación completada!"
	@echo "API disponible en: http://localhost:8080/api"

composer-install: ## Instalar dependencias de Composer
	docker-compose exec app composer install

tinker: ## Abrir Laravel Tinker
	docker-compose exec app php artisan tinker

test: ## Ejecutar tests
	docker-compose exec app php artisan test

artisan: ## Ejecutar comando Artisan (uso: make artisan cmd="route:list")
	docker-compose exec app php artisan $(cmd)
