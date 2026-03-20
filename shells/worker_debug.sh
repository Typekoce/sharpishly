#!/bin/bash

#clear terminal
clear

# 1. Stop the background daemon so it doesn't process the job in the background
echo "🛑 Stopping background worker..."
docker stop sharpishly-worker

# 2. Run the worker script manually in the 'sharpishly-php' container
echo "🤖 Starting Manual Worker Debug (CTRL+C to exit)..."
docker exec -it sharpishly-php php /var/www/html/php/src/Agents/worker.php

# 3. Once you kill the manual process with CTRL+C, restart the background daemon
echo "🚀 Restarting background worker..."
docker start sharpishly-worker