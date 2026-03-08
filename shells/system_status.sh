#!/bin/bash

clear

line='\n-----------------'

# Ollama status
echo $line"Ollama Status"

#systemctl status ollama

# Ollama response from server
echo $line"Ollama Server Response"

curl http://localhost:11434/api/tags

# Docker status
echo $line"Docker Status"

docker ps
