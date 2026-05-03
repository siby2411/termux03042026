#!/bin/bash
# Configuration pour Omega Pharma Dakar
DB_NAME="pharmacie"
BACKUP_DIR="/root/shared/htdocs/apachewsl2026/pharmacie/backups"
DATE=$(date +%Y-%m-%d_%Hh%M)

mkdir -p $BACKUP_DIR

# Dump avec gestion des erreurs
if mysqldump -u root $DB_NAME > $BACKUP_DIR/backup_$DATE.sql; then
    gzip $BACKUP_DIR/backup_$DATE.sql
    echo "Succès : Sauvegarde compressée créée dans $BACKUP_DIR"
else
    echo "Erreur : La sauvegarde a échoué"
fi
