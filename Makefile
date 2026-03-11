# Sharpishly OS - Infrastructure Control Panel
# Location: Project Root

include .env
export

.PHONY: deploy build backup restore vpn-phone vpn-laptop tidy refresh help

# --- 🚀 DEPLOYMENT & PROVISIONING ---

deploy:
	@echo "🛡️ Validating Environment..."
	@chmod +x scripts/provision-check.sh
	@./scripts/provision-check.sh
	@echo "🏗️ Building and Starting Containers..."
	@docker-compose up -d --build --remove-orphans
	@echo "✅ System Live. Checking Heartbeat..."
	@sleep 5
	@curl -f http://localhost:8080/php/pulse || (echo "❌ Health Check Failed!"; exit 1)

build:
	@docker-compose build --pull

# --- 💾 DATA & BACKUPS ---

backup:
	@echo "📦 Creating Database Snapshot..."
	@mkdir -p storage/backups
	@docker exec sharpishly-db mysqldump -u root -p$(DB_PASSWORD) $(DB_NAME) | gzip > storage/backups/db_$(shell date +%F_%H%M).sql.gz
	@echo "✅ Backup saved to storage/backups/"

restore:
	@echo "⚠️ RESTORE: Enter filename (e.g., storage/backups/db_file.sql.gz): "
	@read FILE; \
	gunzip < $$FILE | docker exec -i sharpishly-db mysql -u root -p$(DB_PASSWORD) $(DB_NAME)
	@echo "✅ Database Restored."

# --- 🔐 VPN & ACCESS ---

vpn-phone:
	@echo "📱 Showing QR Code for Mobile Access..."
	@docker exec -it sharpishly-vpn /app/show-peer myphone

vpn-laptop:
	@echo "💻 Retrieving Laptop Configuration..."
	@docker exec -it sharpishly-vpn cat /config/peer_mylaptop/peer_mylaptop.conf

# --- 🧹 MAINTENANCE & LOGS ---

tidy:
	@echo "🧹 Cleaning up logs and docker cache..."
	@find ./storage/logs -name "*.log" -mtime +7 -delete
	@docker system prune -f
	@echo "✨ System Tidy."

refresh:
	@echo "♻️ Restarting PHP and clearing Smarty cache..."
	@docker-compose restart php
	@docker-compose exec php rm -rf /var/www/html/storage/smarty_cache/*
	@echo "✅ Refresh complete."

# --- ❓ HELP ---

help:
	@echo "Sharpishly OS Command List:"
	@echo "  make deploy      - Safe, validated deployment (The Friday Button)"
	@echo "  make backup      - Create a timestamped DB snapshot"
	@echo "  make vpn-phone   - Display QR code for WireGuard"
	@echo "  make refresh     - Clear caches and restart PHP"
	@echo "  make tidy        - Delete old logs and unused Docker layers"