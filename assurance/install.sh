#!/bin/bash

echo "Installation de l'application d'assurance..."

# Création base de données
mysql -u root -p < config/database.sql

# Vérification installation
if [ $? -eq 0 ]; then
    echo "✅ Base de données créée avec succès"
else
    echo "❌ Erreur lors de la création de la base de données"
    exit 1
fi

# Définir les permissions
chmod -R 755 .
chmod 777 logs temp

echo "✅ Installation terminée !"
echo "🌐 Accédez à l'application: http://localhost/assurance/"
echo "👤 Identifiants par défaut: admin / admin123"
