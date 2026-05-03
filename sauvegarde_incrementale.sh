#!/bin/bash
# --- OMEGA SECURE BACKUP (Version Contournement) ---
DATE=$(date +%Y%m%d_%H%M%S)
DEST="/sdcard/Backups_OMEGA_2026/backup_$DATE"

echo "💾 Début de la sauvegarde OMEGA vers Android..."
mkdir -p "$DEST/files"

# 1. Sauvegarde des fichiers
rsync -av --delete /root/shared/htdocs/apachewsl2026/ "$DEST/files/"

# 2. Sauvegarde des bases de données (Via l'utilitaire interne)
# On tente d'utiliser l'utilitaire système par défaut de MariaDB
mysqldump --all-databases --user=root --socket=/var/run/mysqld/mysqld.sock > "$DEST/all_databases_$DATE.sql"

if [ $? -eq 0 ]; then
    echo "✅ Sauvegarde réussie dans : $DEST"
else
    echo "⚠️ Erreur SQL. Tentative avec le binaire direct..."
    /usr/bin/mysqldump --all-databases --user=root --socket=/var/run/mysqld/mysqld.sock > "$DEST/all_databases_$DATE.sql"
fi
