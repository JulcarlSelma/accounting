.PHONY: php
.PHONY: mysql
.PHONY: nginx

# General commands
up:
	docker compose -f compose.yml up -d --build
down:
	docker compose -f compose.yml down --remove-orphans
down-v:
	docker compose down --remove-orphans --volumes
ps:
	docker compose ps
logs:
	docker compose logs
logs-watch:
	docker compose logs --follow
log-nginx:
	docker compose logs nginx
log-nginx-watch:
	docker compose logs --follow nginx
log-php:
	docker compose logs php
log-php-watch:
	docker compose logs --follow php
nginx:
	docker compose exec nginx sh
php:
	docker compose exec php bash
pint:
	docker compose exec php bash vendor/bin/pint
migrate:
	@read -p "WARNING: This will Migrate a new migration. Continue? [y/N] " ans; \
	if [[ "$$ans" =~ ^[Yy]$$ ]]; then \
		echo "Running migrations..."; \
		docker compose exec php php artisan migrate; \
	else \
		echo "Aborted."; \
	fi
migrate-fresh-seed:
	@read -p "WARNING: This will DROP ALL tables. Continue? [y/N] " ans; \
	if [[ "$$ans" =~ ^[Yy]$$ ]]; then \
		echo "Running migrate:fresh --seed..."; \
		docker compose exec php php artisan migrate:fresh --seed; \
	else \
		echo "Aborted."; \
	fi
db-seed:
	@read -p "WARNING: This will UPDATE ALL your database records. Continue? [y/N] " ans; \
	if [[ "$$ans" =~ ^[Yy]$$ ]]; then \
		docker compose exec php php artisan db:seed; \
	else \
		echo "Aborted."; \
	fi
mysql:
	docker compose exec mysql bash
sql:
	docker compose exec mysql bash -c 'mysql -u root -p$$MYSQL_PASSWORD'
