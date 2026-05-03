#!/bin/bash
echo "🛵 Démarrage Omega Scooter"
pkill -9 php 2>/dev/null
cd /root/shared/htdocs/apachewsl2026/scooter
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS scooter_db" 2>/dev/null
mysql -u root -p scooter_db < database/schema.sql 2>/dev/null
php -S 0.0.0.0:8081 -t . > /tmp/scooter.log 2>&1 &
echo "✅ Scooter sur http://localhost:8081"
echo "🔑 Identifiants: admin / Admin@2026"
