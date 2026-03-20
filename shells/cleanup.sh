#!/bin/bash

echo "🧹 Starting Project Consolidation..."

# 1. Move UI assets to the active 'website' folder
echo "🚚 Moving Dashboard assets to /website..."
cp -n public/index.html website/dashboard.html 2>/dev/null
cp -r public/css/* website/css/ 2>/dev/null
cp -r public/js/* website/js/ 2>/dev/null

# 2. Move Agent logic to the active 'php/src' folder
echo "🚚 Moving Agent logic to /php/src/Agents..."
cp -r server/* php/src/Agents/ 2>/dev/null

# 3. Synchronize the Nervous System bridge
# Moving it to your php folder so Nginx can find it at /php/nervous_system.php
cp server/nervous_system.php php/nervous_system.php 2>/dev/null

# 4. Cleanup redundant temporary folders
echo "🗑️  Removing redundant folders..."
rm -rf public
rm -rf server

# 5. Fix permissions for the Queue and Vault
echo "🔐 Refreshing permissions..."
chmod -R 777 storage/queue storage/vault logs php/uploads

echo "✅ Consolidation Complete!"
echo "------------------------------------------------"
echo "NEW MAPPING:"
echo "Dashboard UI:   website/dashboard.html"
echo "Agent Heart:    php/nervous_system.php"
echo "Agent Logic:    php/src/Agents/worker.php"
echo "------------------------------------------------"
