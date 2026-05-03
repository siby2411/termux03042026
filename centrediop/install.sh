#!/bin/bash

echo "🚀 Installation du système de dossier médical Omega Informatique"
echo "================================================================"

# Installation des dépendances
echo "📦 Installation des dépendances NPM..."
npm install

# Création de la base de données
echo "🗄️  Création de la base de données..."
mysql -u root -p <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS omega_medical;
USE omega_medical;
MYSQL_SCRIPT

# Lancer les migrations (à adapter selon votre ORM)
echo "🔄 Migration de la base de données..."
npx sequelize-cli db:migrate

# Démarrer le serveur
echo "✅ Installation terminée!"
echo "📝 Pour démarrer le serveur: npm start"
echo "🌐 Accéder au formulaire: http://localhost:3000/edition-dossier"
