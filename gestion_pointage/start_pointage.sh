#!/bin/bash
# On se place explicitement dans le dossier du projet
cd /root/shared/htdocs/apachewsl2026/gestion_pointage

echo "--- Démarrage Omega Pointage ---"
echo "Port: 8081"
echo "URL: http://localhost:8081"

# -S 0.0.0.0 permet l'accès depuis d'autres appareils sur le réseau
# -t . définit le répertoire courant comme racine
php -S 0.0.0.0:8081 -t .
