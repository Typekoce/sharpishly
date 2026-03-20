#!/bin/bash

# Verify the Generator and Axiom transport don't spike RAM
docker exec -it sharpishly-php php -r "
  require 'vendor/autoload.php';
  \$p = new \App\Services\CrmProcessor();
  echo 'Starting Stress Test... Memory: ' . memory_get_usage() . ' bytes\n';
  \$p->processRentCsv('storage/uploads/stress_test.csv');
  echo 'Finished. Peak Memory: ' . memory_get_peak_usage() . ' bytes\n';
"