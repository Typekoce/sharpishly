#!/bin/bash
TIMESTAMP=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_NAME="sharpishly_db_$TIMESTAMP.sql.gz"

echo "📦 Dumping Database..."
docker exec sharpishly-db mysqldump -u root -p"${DB_PASSWORD}" sharpishly_db | gzip > "/tmp/$BACKUP_NAME"

echo "☁️ Shipping to Backblaze B2..."
# Use the S3-compatible CLI or a simple PHP bridge to push the file
php /var/www/html/php/bin/s3-upload.php "/tmp/$BACKUP_NAME" "backups/$BACKUP_NAME"

echo "✅ Backup Complete: $BACKUP_NAME"
rm "/tmp/$BACKUP_NAME"