#!/bin/bash

# Clear terminal
clear

# Docker containers
docker ps

# Devices detection
docker exec sharpishly-php lsusb
