#!/bin/bash

line='-----------'

echo $line"Python Lightweight MVC Server"
# from inside pymvc-server/
docker compose down
docker compose up -d --build
docker logs sharpishly-pymvc --tail 30
# docker compose build
# docker compose up -d