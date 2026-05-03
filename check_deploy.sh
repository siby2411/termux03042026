#!/bin/bash
# ============================================================
# Vérification post-déploiement
# ============================================================
echo "=== VÉRIFICATION DÉPLOIEMENT ==="

echo -e "\n📁 Arborescence pharmacie :"
find "$HOME/shared/htdocs/apachewsl2026/pharmacie" -type d | sort | sed 's|.*/||' | head -40

echo -e "\n📁 Arborescence revendeur_medical :"
find "$HOME/shared/htdocs/apachewsl2026/revendeur_medical" -type d | sort | sed 's|.*/||' | head -40

echo -e "\n🗄 Tables MariaDB — pharmacie :"
mysql -u root -e "USE pharmacie; SHOW TABLES;"

echo -e "\n🗄 Tables MariaDB — revendeur_medical :"
mysql -u root -e "USE revendeur_medical; SHOW TABLES;"

echo -e "\n⚡ Triggers pharmacie :"
mysql -u root -e "USE pharmacie; SHOW TRIGGERS\G" | grep Trigger | awk '{print "  ✓", $2}'

echo -e "\n👁 Vues pharmacie :"
mysql -u root -e "USE pharmacie; SHOW FULL TABLES WHERE Table_type = 'VIEW';"

echo -e "\n🐘 PHP version :"
php -v | head -1

echo -e "\n🌐 Apache status :"
service apache2 status 2>/dev/null | grep -E "(active|running)" || echo "Apache non actif"

echo -e "\n✅ Vérification terminée"
