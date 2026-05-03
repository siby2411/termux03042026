#!/bin/bash

echo "Installation d'Oméga Fitness - Système de Gestion"

# Restart MariaDB
service mariadb restart

# Import base de données
mysql -u root < database.sql

# Création des dossiers nécessaires
mkdir -p config assets/css assets/js assets/images logs

# Définir les permissions
chmod -R 755 .

# Démarrer le serveur PHP
pkill -9 php
php -S localhost:8080 &

echo "Installation terminée!"
echo "Accédez à l'application: http://localhost:8080"
echo "Identifiants par défaut: root / (pas de mot de passe)"
