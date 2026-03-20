#!/bin/bash

line='---------------------'
echo $line"Stopping Factory Workers"
# Send a stop signal to your PHP daemon
touch storage/queue/STOP

echo $line"docker compose down"
docker compose down

echo $line"Flushing AI RAM"
# This tells Ollama to unload all models immediately
curl -s http://localhost:11434/api/generate -d '{"model": "llama3.2:1b", "keep_alive": 0}' > /dev/null

echo $line"SHARPISHLY OFFLINE"
