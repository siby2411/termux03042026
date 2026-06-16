#!/bin/bash
# Script de backup automatique et commit git pour OMEGA ERP

cd /root/shared/htdocs/apachewsl2026/gestion_ecole/

# 1. Sauvegarder la base de données 'ecole'
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root ecole > backup_ecole_${DATE}.sql
gzip -f backup_ecole_${DATE}.sql

# 2. Ajouter les modifications
git add .
git add -f backup_ecole_*.sql.gz

# 3. Commit
git commit -m "Backup $(date '+%Y-%m-%d %H:%M:%S') - Sauvegarde base de données ecole"

# 4. Push
git push origin main

echo "✅ Backup et commit effectués pour la base 'ecole'"
ls -la backup_ecole_*.sql.gz
