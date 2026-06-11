#!/bin/bash
# Aller dans le répertoire du projet
cd /root/shared/htdocs/apachewsl2026/

# Créer un dossier de backup s'il n'existe pas
mkdir -p backups
DATE=$(date +%Y%m%d_%H%M%S)

# 1. Sauvegarder les bases de données dans le dossier backups
echo "Sauvegarde des bases de données..."
mysqldump -u root centrediop > backups/backup_centrediop_${DATE}.sql
mysqldump -u root --all-databases > backups/backup_all_databases_${DATE}.sql

# Compression
gzip -f backups/backup_centrediop_${DATE}.sql
gzip -f backups/backup_all_databases_${DATE}.sql

# 2. Sauvegarder tout le répertoire (fichiers + sauvegardes SQL)
git add .
git add -f backups/backup_*.sql.gz

# 3. Commit
git commit -m "Backup complet du projet et DB - $(date '+%Y-%m-%d %H:%M:%S')"

# 4. Push
git push origin main

echo "✅ Backup, commit et push effectués avec succès."
ls -la backups/backup_*.sql.gz
