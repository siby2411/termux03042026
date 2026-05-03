#!/bin/bash
echo "🔄 Nettoyage des processus PHP sur le port 8085..."
pkill -f "0.0.0.0:8085"

echo "📂 Passage vers le répertoire public..."
cd /root/shared/htdocs/apachewsl2026/report/public

echo "🚀 Lancement de OMEGA Report sur http://localhost:8085"
nohup php -S 0.0.0.0:8085 > /root/php_report.log 2>&1 &

echo "✅ Serveur démarré en arrière-plan."
echo "📝 Log disponible dans /root/php_report.log"
