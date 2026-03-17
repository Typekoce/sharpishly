#!/bin/bash

line='-----------'

echo $line"Python Lightweight MVC Server"
docker compose build
docker compose up -d