#!/bin/bash

# =====================================================
# 🚀 OMEGA MULTISECTORIEL - BACKUP & GIT SYNC SYSTEM
# =====================================================

PROJECT_DIR="/root/shared/htdocs/apachewsl2026"
BACKUP_DIR="$PROJECT_DIR/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

cd $PROJECT_DIR || exit 1

echo "=============================================="
echo "🚀 OMEGA BACKUP SYSTEM - $DATE"
echo "=============================================="

# ===================== BASES DE DONNÉES =====================
DB_LIST=("gestion_produits" "restau" "clinique" "omega_main")

for DB_NAME in "${DB_LIST[@]}"; do

    echo "⏳ Sauvegarde DB : $DB_NAME"

    BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql"

    mysqldump -u root $DB_NAME > $BACKUP_FILE 2>/dev/null

    if [ $? -eq 0 ]; then
        gzip -f $BACKUP_FILE
        echo "✅ OK : $DB_NAME"
    else
        echo "⚠️ ERREUR : $DB_NAME (ignorée)"
    fi

done

# ===================== BACKUP CODE =====================
echo "📦 Sauvegarde du code source..."

tar -czf $BACKUP_DIR/apachewsl2026_code_${DATE}.tar.gz \
--exclude="backups" \
--exclude=".git" \
.

echo "✅ Code sauvegardé"

# ===================== GIT SYNC =====================
echo "📡 Synchronisation GitHub..."

git add .

# ajouter uniquement backups s’ils existent
if ls $BACKUP_DIR/*.gz 1> /dev/null 2>&1; then
    git add $BACKUP_DIR/*.gz
fi

git commit -m "🚀 OMEGA BACKUP ${DATE}" || echo "⚠️ Rien à commit"

git push origin main

echo "=============================================="
echo "✅ BACKUP & SYNC TERMINÉS"
echo "=============================================="
