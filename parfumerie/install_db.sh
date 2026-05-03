#!/bin/bash

echo "📦 Installation de la base de données Cosmetique"
echo "================================================"

# Demander le mot de passe MariaDB
read -sp "Entrez votre mot de passe MariaDB: " MYSQL_PWD
echo ""

# Créer la base de données
mysql -u root -p${MYSQL_PWD} -e "CREATE DATABASE IF NOT EXISTS cosmetique_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if [ $? -eq 0 ]; then
    echo "✅ Base de données 'cosmetique_db' créée"
else
    echo "❌ Erreur lors de la création de la base"
    exit 1
fi

# Importer le schéma
mysql -u root -p${MYSQL_PWD} cosmetique_db < /root/parfumerie/database/schema.sql
if [ $? -eq 0 ]; then
    echo "✅ Tables créées avec succès"
else
    echo "❌ Erreur lors de l'importation"
    exit 1
fi

# Vérifier les tables
echo ""
echo "📋 Tables créées:"
mysql -u root -p${MYSQL_PWD} -e "USE cosmetique_db; SHOW TABLES;"

echo ""
echo "✅ Installation terminée avec succès!"
echo "🔑 Identifiants: admin / Admin@2026"
echo ""
echo "🌐 Démarrez l'application avec: php -S localhost:8080 -t /root/parfumerie"
