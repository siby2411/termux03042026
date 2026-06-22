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
    echo "📦 DB: $DB"
    mysqldump -u root $DB > "$BACKUP_DIR/${DB}_${DATE}.sql" 2>/dev/null

    if [ $? -eq 0 ]; then
        gzip -f "$BACKUP_DIR/${DB}_${DATE}.sql"
    fi
done

# ================= CODE BACKUP LOCAL ONLY =================
echo "📦 Backup local (NON GitHub)..."

tar -czf "$BACKUP_DIR/code_${DATE}.tar.gz" \
--exclude="backups" \
--exclude="uploads" \
--exclude=".git" \
.

echo "⚠️ Archive stockée localement uniquement"

# ================= GIT SAFE =================
git add .

# NE PAS ajouter backups lourds
git add $BACKUP_DIR/*.gz 2>/dev/null

git commit -m "🚀 OMEGA SYNC $DATE" || echo "Rien à commit"
git push origin main

echo "=================================="
echo "✅ SYNC PROPRE TERMINÉ"
echo "=================================="
