#!/bin/bash

# Clear terminal... I'm just picky
clear

# Colors for feedback
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Starting Sharpishly R&D Environment...${NC}"

# 1. Create missing directories before permissions
# Updated to match the structure: php/uploads
mkdir -p php/uploads php/logs

# 2. Use 'docker compose' (modern standard)
if ! docker compose up -d --build; then
    echo -e "${RED}❌ Docker Compose failed to start.${NC}"
    exit 1
fi

# 3. Wait for MySQL to be ready
echo -e "${BLUE}⏳ Waiting for MySQL to become healthy...${NC}"
MAX_RETRIES=30
COUNT=0
until [ "$(docker inspect -f {{.State.Health.Status}} sharpishly-db 2>/dev/null)" == "healthy" ]; do
    echo -n "."
    sleep 2
    COUNT=$((COUNT+1))
    if [ $COUNT -ge $MAX_RETRIES ]; then
        echo -e "${RED}\n❌ MySQL health check timed out.${NC}"
        exit 1
    fi
done
echo -e "\n${GREEN}✅ MySQL is up!${NC}"

# 4. Run Database Migrations
echo -e "${BLUE}📦 Running database migrations...${NC}"
docker exec -i sharpishly-db mysql -uuser -ppass sharpishly < php/src/migrate.sql

# 5. Fix Permissions
echo -e "${BLUE}🔒 Setting folder permissions...${NC}"
sudo chmod -R 777 php/uploads php/logs

# 6. Restart Worker to pick up latest code changes
echo -e "${BLUE}🔄 Restarting background worker...${NC}"
docker restart sharpishly-worker

echo -e "${GREEN}✨ Setup Complete! System is live at http://localhost:8080${NC}"