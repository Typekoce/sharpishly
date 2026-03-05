#!/bin/bash
clear
docker exec -it sharpishly-php php /var/www/html/php/src/worker-daemon.php
#tail -f php/logs/app.log
