#!/bin/bash
# Save as: dev-up.sh

line=$'\n-----------------\n'
clear

echo "🚀 Building and Starting Sharpishly Infrastructure..."
docker compose up -d --build

echo "${line}🔍 Running Python Hardware Check..."
# Give the container a second to run start.sh before exec
sleep 3
docker exec sharpishly-php python3 /var/www/html/python/usb_scanner.py --once || echo "⚠️ Scanner script not found yet."

echo "${line}🧠 Ollama Health Check"
# Check if Ollama is responding on the host
curl -s http://localhost:11434/api/tags | grep -q "models" && echo "✅ Ollama is Responding" || echo "❌ Ollama Connection Failed"

echo "${line}🛠️ Worker Debug Flow"
echo "🛑 Stopping background worker container..."
docker stop sharpishly-worker

echo "🤖 Starting Manual Worker Debug (CTRL+C to exit)..."
docker exec -it sharpishly-php php /var/www/html/php/src/Agents/worker.php

echo "🚀 Restarting background worker..."
docker start sharpishly-worker

echo "${line}📊 Current Container Status"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"