#!/bin/bash
# Location: shells/dev-up.sh

line=$'\n-----------------\n'
clear

echo "🚀 Recreating Sharpishly Infrastructure..."
docker compose up -d --force-recreate

echo "${line}⚙️  Verifying PHP Upload Limits..."
docker exec sharpishly-php php -r "echo 'Upload Max: ' . ini_get('upload_max_filesize') . \"\n\";"
docker exec sharpishly-php php -r "echo 'Post Max: ' . ini_get('post_max_size') . \"\n\";"

echo "${line}📂 Aligning Storage Permissions..."
# Use absolute container paths to avoid ambiguity
docker exec sharpishly-php mkdir -p /var/www/html/storage/uploads /var/www/html/storage/queue /tmp
docker exec sharpishly-php chmod -R 777 /var/www/html/storage/
docker exec sharpishly-php chmod 1777 /tmp

echo "✅ Storage synced and permissions set to 777"

echo "${line}🧠 Ollama Health Check"
curl -s http://localhost:11434/api/tags | grep -q "models" && echo "✅ Ollama is Responding" || echo "❌ Ollama Connection Failed"

echo "${line}🛠️ Worker Daemon Launch"
# Launching with absolute path to ensure autoloading is consistent
echo "🤖 Starting Manual Worker Interrogation (CTRL+C to exit)..."
docker exec -it sharpishly-php php /var/www/html/php/src/worker-daemon.php