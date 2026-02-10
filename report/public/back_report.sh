#!/bin/bash

# Variables
BACKUP_DIR="/mnt/e/report2026"
DB_NAME="synthesepro_db"
DB_USER="root"
DB_PASS="123"
WEB_DIR="/var/www/report"
DATE=$(date +"%Y%m%d_%H%M%S")

# Création des sous-répertoires si nécessaire
mkdir -p "$BACKUP_DIR/web"
mkdir -p "$BACKUP_DIR/sql"

# Sauvegarde du répertoire web
tar -czf "$BACKUP_DIR/web/report_$DATE.tar.gz" -C "$WEB_DIR" .

# Sauvegarde de la base de données
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/sql/${DB_NAME}_$DATE.sql"

echo "Sauvegarde terminée : $DATE"

