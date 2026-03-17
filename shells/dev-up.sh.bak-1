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
docker exec sharpishly-php mkdir -p \
    /var/www/html/storage/uploads \
    /var/www/html/storage/queue \
    /var/www/html/storage/logs \
    /tmp

# Initialize all log files
docker exec sharpishly-php touch \
    /var/www/html/storage/logs/php_error.log \
    /var/www/html/storage/logs/nginx_access.log \
    /var/www/html/storage/logs/nginx_error.log \
    /var/www/html/storage/logs/mysql_error.log \
    /var/www/html/storage/logs/app.log \
    /var/www/html/storage/logs/scheduler.log

# SET PERMISSIONS & OWNERSHIP
docker exec sharpishly-php chown -R 1000:33 /var/www/html/storage/
docker exec sharpishly-php chmod -R 777 /var/www/html/storage/
docker exec sharpishly-php chmod 666 /var/www/html/storage/logs/mysql_error.log
docker exec sharpishly-php chmod 1777 /tmp

echo "✅ Storage synced. Logs redirected to storage/logs/"

echo "${line}🧠 Ollama Health Check"
curl -s http://localhost:11434/api/tags | grep -q "models" && echo "✅ Ollama is Responding" || echo "❌ Ollama Connection Failed"

echo "${line}🧪 Automated Quality Gate"
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
# Using -it for interactive control and pointing to the structured Agents path
docker exec -it sharpishly-php php /var/www/html/php/src/Agents/worker.php
