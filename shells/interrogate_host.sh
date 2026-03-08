#!/usr/bin/env bash

# Colors for the "God Mode" aesthetic
CYAN='\033[0;36m'
GOLD='\033[0;33m'
NC='\033[0m' # No Color

clear
echo -e "${GOLD}📡 SHARPISHLY NEURAL INTERROGATION INITIALIZED...${NC}"
echo -e "${CYAN}Target Container: sharpishly-php${NC}"
echo "--------------------------------------------------"

# 1. Physical Layer: USB & PCI
echo -e "\n${GOLD}[1. PHYSICAL TOPOLOGY]${NC}"
docker exec sharpishly-php lsusb | awk '{print "USB Device: " $0}'
docker exec sharpishly-php lspci -vmm | grep -E "Class|Vendor|Device" | sed 's/^/  /'

# 2. Storage Layer: Disk Topology
echo -e "\n${GOLD}[2. STORAGE ARCHITECTURE]${NC}"
docker exec sharpishly-php lsblk -o NAME,SIZE,TYPE,MODEL,SERIAL

# 3. Network Layer: Logic & Routing
echo -e "\n${GOLD}[3. NETWORK INTERFACES]${NC}"
docker exec sharpishly-php ip -br addr show

# 4. Neural Layer: CPU & Memory
echo -e "\n${GOLD}[4. COMPUTATIONAL CAPACITY]${NC}"
docker exec sharpishly-php lscpu | grep -E "Model name|CPU\(s\):|Thread"
docker exec sharpishly-php free -h | grep -E "Mem|Swap"

echo -e "\n--------------------------------------------------"
echo -e "${GOLD}INTERROGATION COMPLETE. SYSTEM DNA MAPPED.${NC}"
