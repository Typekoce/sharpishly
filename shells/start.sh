#!/bin/bash
# Save as: shells/start.sh

echo "📦 Provisioning Container Environment..."

# 1. Update and install system-level USB libraries
apt-get update && apt-get install -y \
    usbutils \
    libusb-1.0-0-dev \
    python3-pip \
    && rm -rf /var/lib/apt/lists/*

# 2. Install Python hardware communication library
pip3 install pyusb --break-system-packages

# 3. Start PHP-FPM in the background
echo "🐘 Starting PHP-FPM..."
php-fpm -D

# 4. Keep container alive and start the USB Scanner
echo "🐍 Starting Sensory Layer: USB Scanner..."
# We use 'exec' here so Python becomes PID 1 and receives shutdown signals
exec python3 /var/www/html/python/usb_scanner.py

# To see the QR code in your terminal (perfect for mobile)
docker exec -it sharpishly-vpn /app/show-peer myphone

# To copy the text config for your laptop
docker exec -it sharpishly-vpn cat /config/peer_mylaptop/peer_mylaptop.conf