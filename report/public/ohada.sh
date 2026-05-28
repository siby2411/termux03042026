#!/bin/bash
# Script de redémarrage des services pour SYSCOHADA PRO

echo "🔄 Redémarrage de MariaDB..."
 service mariadb restart

echo "🛑 Arrêt des processus PHP existants..."
pkill -9 php

cd /root/shared/htdocs/apachewsl2026/report/public/
echo "🚀 Démarrage du serveur PHP sur le port 8000..."
php -S 0.0.0.0:8000 > /dev/null 2>&1 &

sleep 2
echo "✅ Services redémarrés."
echo "🔗 Accédez à l'application : http://127.0.0.1:8000/login.php"
