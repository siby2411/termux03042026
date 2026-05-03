#!/bin/bash

echo "🍕 Démarrage de Omega Pizzeria"
echo "================================"

# Arrêter les anciens processus PHP
pkill -9 php 2>/dev/null

# Aller dans le répertoire
cd /root/shared/htdocs/apachewsl2026/pizzeria

# Vérifier que la base existe
if ! mysql -u root -e "USE pizzeria_db" 2>/dev/null; then
    echo "❌ Base de données non trouvée! Exécutez d'abord: bash install.sh"
    exit 1
fi

# Démarrer le serveur PHP
echo "🔄 Démarrage du serveur PHP sur le port 8080..."
php -S 0.0.0.0:8080 -t /root/shared/htdocs/apachewsl2026/pizzeria > /tmp/pizzeria.log 2>&1 &

# Attendre que le serveur démarre
sleep 2

# Vérifier si le serveur est en marche
if curl -s http://localhost:8080 > /dev/null 2>&1; then
    echo ""
    echo "✅ Application démarrée avec succès!"
    echo "🌐 Accédez à: http://localhost:8080"
    echo "🔑 Identifiants: admin / Admin@2026"
    echo ""
    echo "📋 Logs: tail -f /tmp/pizzeria.log"
    echo "🛑 Arrêter: pkill -9 php"
else
    echo "❌ Erreur: Le serveur PHP n'a pas démarré!"
    echo "📋 Consultez les logs: cat /tmp/pizzeria.log"
fi
