#!/bin/bash
docker stop sharpishly-worker
docker exec -it sharpishly-worker php /var/www/html/php/src/Agents/worker.php
docker start sharpishly-worker