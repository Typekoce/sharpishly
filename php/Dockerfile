# STAGE 1: Build & Validate
FROM php:8.3-fpm-alpine AS builder
COPY . /var/www/html
#RUN composer install --no-dev --optimize-autoloader
# Run a syntax check on all PHP files before proceeding
RUN find . -name "*.php" -exec php -l {} \; | grep "No syntax errors"

# STAGE 2: Production (Only reached if Stage 1 passes)
FROM php:8.3-fpm-alpine
COPY --from=builder /var/www/html /var/www/html
# ... set permissions ...
# After copying files, but before finishing the image
RUN find . -type f -name "*.php" -print0 | xargs -0 -n1 -P4 php -l | grep -v "No syntax errors detected" && exit 1 || true