#!/bin/bash
# Script de Backup et Synchronisation pour OMEGA MULTISECTORIEL

# Définition des répertoires
PROJECT_DIR="/root/shared/htdocs/apachewsl2026
DB_NAME="omega_multisectoriel"

# Se placer dans le répertoire
cd $PROJECT_DIR

# 1. Sauvegarder la base de données
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="backup_${DB_NAME}_${DATE}.sql"

echo "⏳ Sauvegarde de la base $DB_NAME en cours..."
mysqldump -u root $DB_NAME > $BACKUP_FILE
gzip -f $BACKUP_FILE

# 2. Préparer le dépôt Git
git add .
git add -f backup_${DB_NAME}_*.sql.gz

# 3. Commit et Push
COMMIT_MSG="Backup ${DB_NAME} - $(date '+%Y-%m-%d %H:%M:%S')"
git commit -m "$COMMIT_MSG"
git push origin main

echo "✅ Backup et commit effectués pour le projet : $DB_NAME"
ls -lh backup_${DB_NAME}_*.sql.gz
