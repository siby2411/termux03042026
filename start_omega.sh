#!/bin/bash
# --- OMEGA AUTO-REPAIR & START ---
# Ce script garantit un démarrage propre pour la livraison client

echo "🛠️  Vérification de l'intégrité du système..."

# 1. Nettoyage des résidus de crash (PID et Sockets)
pkill -9 mysql > /dev/null 2>&1
pkill -9 mysqld > /dev/null 2>&1
rm -f /var/lib/mysql/*.pid
rm -f /var/run/mysqld/mysqld.sock

# 2. Préparation de l'environnement
mkdir -p /var/run/mysqld
chmod 777 /var/run/mysqld

# 3. Lancement du moteur MariaDB
# Note: Redirection vers /dev/null pour un démarrage silencieux et propre
mysqld_safe --datadir=/var/lib/mysql --user=root --skip-grant-tables > /dev/null 2>&1 &

# 4. Boucle d'attente intelligente (Timeout 15s)
echo -n "🚀 Démarrage de MySQL "
for i in {1..15}; do
    if [ -S /var/run/mysqld/mysqld.sock ]; then
        chmod 777 /var/run/mysqld/mysqld.sock
        echo -e "\n✅ [OMEGA] Système prêt pour la livraison."
        echo "🔗 http://127.0.0.1:9080"
        exit 0
    fi
    echo -n "."
    sleep 1
done

echo -e "\n❌ Erreur : Le moteur MySQL ne répond pas. Vérifiez les logs."
exit 1
