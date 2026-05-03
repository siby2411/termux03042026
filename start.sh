#!/bin/bash

echo "🚀 Démarrage de l'application Cosmetique..."

# Vérifier si la base existe
if ! mysql -u root -e "USE cosmetique_db" 2>/dev/null; then
    echo "❌ Base de données non trouvée!"
    echo "📦 Exécutez d'abord: bash install_db.sh"
    exit 1
fi

# Démarrer le serveur PHP
cd /root/parfumerie

# Tuer les processus PHP existants
pkill -9 php 2>/dev/null

# Démarrer le serveur
php -S 0.0.0.0:8080 -t . > /tmp/php_server.log 2>&1 &

echo ""
echo "✅ Application démarrée avec succès!"
echo "🌐 Accédez à: http://localhost:8080"
echo "🔑 Identifiant: admin | Mot de passe: Admin@2026"
echo ""
echo "📋 Logs: tail -f /tmp/php_server.log"
echo "🛑 Arrêter: pkill -9 php"
