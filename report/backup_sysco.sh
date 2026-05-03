#!/bin/bash

# Variables
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/mnt/e/reporting2026"
WEB_DIR="/var/www/reporting"
DB_NAME="synthesepro_db"   # ou sysco_ohada
DB_USER="root"             # adapter si nécessaire
DB_PASS="123"             # adapter si nécessaire

mkdir -p "$BACKUP_DIR"

# Backup du répertoire reporting
tar -czf "$BACKUP_DIR/reporting_files_$TIMESTAMP.tar.gz" -C "$WEB_DIR" .

# Backup de la base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_DIR/${DB_NAME}_$TIMESTAMP.sql"

echo "Sauvegarde terminée : $TIMESTAMP"

