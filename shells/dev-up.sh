#!/bin/bash
# Location: shells/dev-up.sh

line=$'\n-----------------\n'
clear

echo "🛑 Stopping bare metal Ollama Service..."
sudo systemctl stop ollama 2>/dev/null || echo "Ollama not running on host."

echo "🚀 Recreating Sharpishly Infrastructure..."
#docker compose up -d --force-recreate
# 1. Be explicit about the file — this bypasses auto-discovery
docker compose -f docker-compose.yml down
docker compose -f docker-compose.yml up -d

echo "${line}⚙️  Verifying PHP Upload Limits..."
docker exec sharpishly-php php -r "echo 'Upload Max: ' . ini_get('upload_max_filesize') . \"\n\";"

echo "${line}📂 Aligning Storage Permissions..."
# CRITICAL: Re-establishing your directory structure
docker exec sharpishly-php mkdir -p \
    /var/www/html/storage/uploads \
    /var/www/html/storage/queue \
    /var/www/html/storage/logs \
    /tmp

# CRITICAL: Restoring your log initialization
docker exec sharpishly-php touch \
    /var/www/html/storage/logs/php_error.log \
    /var/www/html/storage/logs/nginx_access.log \
    /var/www/html/storage/logs/nginx_error.log \
    /var/www/html/storage/logs/mysql_error.log \
    /var/www/html/storage/logs/app.log \
    /var/www/html/storage/logs/scheduler.log

# CRITICAL: Restoring your specific Ownership & Permission levels
docker exec sharpishly-php chown -R 1000:33 /var/www/html/storage/
docker exec sharpishly-php chmod -R 777 /var/www/html/storage/
docker exec sharpishly-php chmod 666 /var/www/html/storage/logs/mysql_error.log
docker exec sharpishly-php chmod 1777 /tmp

echo "✅ Storage synced. Logs redirected and permissions locked."

echo "${line}🧠 Neural Memory Initialization (Qdrant)"
# Check if collection exists to avoid error noise
COLLECTION_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:6333/collections/properties)
if [ "$COLLECTION_STATUS" -ne 200 ]; then
    echo "📦 Creating 'properties' vector collection..."
    curl -X PUT "http://localhost:6333/collections/properties" \
         -H "Content-Type: application/json" \
         --data '{ "vectors": { "size": 768, "distance": "Cosine" } }'
else
    echo "✅ Qdrant collection 'properties' is active."
fi

echo "${line}🧠 Ollama Model Check"
# Ensure the embedding model is available inside the container
if ! docker exec sharpishly-ollama ollama list | grep -q "nomic-embed-text"; then
    echo "📥 Pulling nomic-embed-text embedding model..."
    docker exec sharpishly-ollama ollama pull nomic-embed-text
else
    echo "✅ Ollama is Responding and Models are Loaded."
fi

echo "${line}🧪 Automated Quality Gate"
docker exec sharpishly-php php /var/www/html/tests/run.php
if [ $? -ne 0 ]; then
    echo "❌ CRITICAL: Tests failed!"
    read -p "Do you want to launch the worker anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then exit 1; fi
fi

echo "${line}🛠️ Worker Daemon Launch"
docker exec -it sharpishly-php php /var/www/html/php/src/Agents/worker.php