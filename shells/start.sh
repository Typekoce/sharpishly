#!/bin/bash
clear
# Use a real newline in the variable
line=$'\n-----------------\n'

docker compose up -d --build

# Run Python test
docker exec sharpishly-php python3 /var/www/html/python/hello.py

# Ollama check
echo "${line}Ollama Server Response"
curl -s http://localhost:11434/api/tags | grep -q "models" && echo "✅ Ollama is Responding" || echo "❌ Ollama Connection Failed"

# Worker Debug Flow
echo "${line}Worker Management"
echo "🛑 Stopping background worker container..."
docker stop sharpishly-worker

echo "🤖 Starting Manual Worker Debug (CTRL+C to exit)..."
# Note: Added path check
docker exec -it sharpishly-php php /var/www/html/php/src/Agents/worker.php

echo "🚀 Restarting background worker..."
docker start sharpishly-worker

echo "${line}Current Container Status"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"