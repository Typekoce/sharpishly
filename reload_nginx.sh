#!/bin/bash
docker exec sharpishly-app nginx -s reload
docker exec -it sharpishly-php ls -la /var/www/html/php/index.php
docker logs sharpishly-app | grep error
docker logs sharpishly-php | grep -i "file not found\|unknown\|error"
curl http://192.168.0.11:8080/php/home/response
curl http://192.168.0.11:8080/php/home/index
