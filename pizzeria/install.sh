#!/bin/bash

echo "🍕 Installation de l'application Pizzeria"
echo "========================================"

cd /root/shared/htdocs/apachewsl2026/pizzeria

# Création des dossiers
mkdir -p uploads/{products,temp,wave_qr,reservations} logs
chmod -R 777 uploads

# Installation de la base de données
echo "📦 Installation de la base de données..."
mysql -u root -p -e "DROP DATABASE IF EXISTS pizzeria_db; CREATE DATABASE pizzeria_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p pizzeria_db < database/schema.sql

echo ""
echo "✅ Installation terminée avec succès!"
echo "🔑 Identifiants: admin / Admin@2026"
echo ""
echo "🚀 Pour démarrer: cd /root/shared/htdocs/apachewsl2026/pizzeria && php -S 0.0.0.0:8080 -t ."
