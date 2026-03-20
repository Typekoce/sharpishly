#!/bin/bash
echo "🛑 Stopping local Ollama services..."
sudo systemctl stop ollama
sudo systemctl disable ollama

echo "🗑️ Removing binary and service files..."
sudo rm $(which ollama)
sudo rm /etc/systemd/system/ollama.service

echo "🧹 Cleaning up local models (Docker will pull its own)..."
# We keep your llama3.1 download if you want to move it later, 
# but Docker's Ollama usually stores models in its own volume.
sudo rm -rf /usr/share/ollama

echo "✅ Local Ollama purged. Ready for Docker dominance."