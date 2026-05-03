#!/bin/bash
# ============================================================
#  OMEGA INFORMATIQUE CONSULTING — GESTION CHARCUTERIE
#  Script d'installation automatique
#  Usage: bash install.sh
# ============================================================

BASE="/root/shared/htdocs/apachewsl2026/charcuterie1"
DB_NAME="charcuterie1"
DB_USER="root"
DB_PASS=""

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║   🥩  OMEGA INFORMATIQUE CONSULTING                     ║"
echo "║   🏆  Installation Gestion Charcuterie                  ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# 1. Créer la base si besoin
echo "▶ Création/vérification de la base de données..."
mariadb -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then echo "  ✅ Base '$DB_NAME' prête"; else echo "  ❌ Erreur création base"; exit 1; fi

# 2. Importer le schéma
echo "▶ Import du schéma SQL et données..."
mariadb -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < "$BASE/schema.sql" 2>/dev/null
if [ $? -eq 0 ]; then echo "  ✅ Schéma importé avec succès"; else echo "  ❌ Erreur import SQL"; exit 1; fi

# 3. Permissions dossiers
echo "▶ Configuration des permissions..."
mkdir -p "$BASE/assets/uploads"
chmod 755 "$BASE/assets/uploads"
chmod 755 "$BASE/assets"
echo "  ✅ Dossier uploads configuré"

# 4. Vérifier PHP
echo "▶ Vérification PHP..."
PHP_VER=$(php -r "echo PHP_VERSION;" 2>/dev/null)
if [ -n "$PHP_VER" ]; then
    echo "  ✅ PHP $PHP_VER détecté"
else
    echo "  ⚠️  PHP non trouvé dans PATH"
fi

# 5. Extensions PHP
echo "▶ Extensions PHP requises..."
for ext in pdo pdo_mysql mbstring gd fileinfo session; do
    if php -m 2>/dev/null | grep -qi "^$ext$"; then
        echo "  ✅ $ext"
    else
        echo "  ⚠️  $ext — non chargée (peut causer des erreurs)"
    fi
done

# 6. Résumé
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║   ✅  INSTALLATION TERMINÉE AVEC SUCCÈS                 ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║                                                          ║"
echo "║   🌐 Site vitrine :                                      ║"
echo "║   http://localhost/apachewsl2026/charcuterie1/            ║"
echo "║                                                          ║"
echo "║   🔐 Administration :                                    ║"
echo "║   http://localhost/apachewsl2026/charcuterie1/admin/      ║"
echo "║                                                          ║"
echo "║   📧 Email  : admin@omega.com                            ║"
echo "║   🔑 Mot de passe : password                             ║"
echo "║                                                          ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
