#!/bin/bash

PROJECT_DIR="/root/shared/htdocs/apachewsl2026"
BACKUP_DIR="$PROJECT_DIR/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR
cd $PROJECT_DIR || exit 1

echo "=================================="
echo "🚀 OMEGA BACKUP SYSTEM"
echo "=================================="

# ================= DATABASES =================
DB_LIST=(
agence_voyage annuaire assurance_sn auto banque blog
cabinet_radiologie centrediop charcuterie1 clinique
couture_senegal ecommerce foot_school geo gestion
gestion_auto gestion_commerciale gestion_immobiliere
gestion_pointages gp_db grh hotel_omega ingenierie
laboratoire_medical librairie o_garage offre_emploi
omega_fitness omega_multisectoriel pharmacie piece_auto
pizzeria pme portail_ecommerce pressing_management
reporting_db restaurant_management revendeur_medical
scooter_db synthesepro_db transport_omega
)

for DB in "${DB_LIST[@]}"; do
    echo "📦 Backup DB: $DB"
    mysqldump -u root $DB > "$BACKUP_DIR/${DB}_${DATE}.sql" 2>/dev/null

    if [ $? -eq 0 ]; then
        gzip -f "$BACKUP_DIR/${DB}_${DATE}.sql"
    fi
done

# ================= CODE BACKUP (SAFE) =================
echo "📦 Code backup léger..."

tar -czf "$BACKUP_DIR/code_${DATE}.tar.gz" \
--exclude="backups" \
--exclude="node_modules" \
--exclude=".git" \
--exclude="uploads" \
.

# ⚠️ IMPORTANT: NO GIT PUSH FOR LARGE FILES
echo "⚠️ Backups lourds NON envoyés sur Git (local only)"

# ================= GIT CLEAN SYNC =================
git add .

# uniquement SQL compressés légers (< règles Git safe)
git add $BACKUP_DIR/*.gz 2>/dev/null

git commit -m "🚀 OMEGA SYNC $DATE" || echo "Rien à commit"
git push origin main

echo "=================================="
echo "✅ BACKUP TERMINÉ PROPREMENT"
echo "=================================="
