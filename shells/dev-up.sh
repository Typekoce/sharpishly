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
docker exec sharpishly-php mkdir -p /var/www/html/storage/uploads /var/www/html/storage/queue /var/www/html/storage/logs /tmp
docker exec sharpishly-php chmod -R 777 /var/www/html/storage/
docker exec sharpishly-php chmod 1777 /tmp
echo "✅ Storage synced and permissions set to 777"

echo "${line}🧠 Ollama Health Check"
curl -s http://localhost:11434/api/tags | grep -q "models" && echo "✅ Ollama is Responding" || echo "❌ Ollama Connection Failed"

echo "${line}🧪 Automated Quality Gate"
# Running the new TestRunner suite
docker exec sharpishly-php php /var/www/html/tests/run.php
TEST_EXIT_CODE=$?

if [ $TEST_EXIT_CODE -ne 0 ]; then
    echo "❌ CRITICAL: Tests failed!"
    read -p "Do you want to launch the worker anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Exiting to fix errors..."
        exit 1
    fi
else
    echo "✅ Tests Passed. System is stable."
fi

echo "${line}🛠️ Worker Daemon Launch"
echo "🤖 Starting Manual Worker Interrogation (CTRL+C to exit)..."
# Absolute path to ensure Registry and Autoloader alignment
docker exec -it sharpishly-php php /var/www/html/php/src/worker-daemon.php