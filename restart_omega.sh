#!/bin/bash

# --- Nettoyage agressif ---
fuser -k 8080/tcp 9080/tcp 3306/tcp 2>/dev/null
killall -9 apache2 httpd mysqld 2>/dev/null
sleep 1

# --- Démarrage MySQL ---
/etc/init.d/mysql start || mysqld_safe --user=root &
sleep 2

# --- Démarrage Apache ---
# On utilise apache2ctl si présent, sinon le binaire direct
if command -v apache2ctl >/dev/null; then
    apache2ctl start
else
    export APACHE_RUN_USER=www-data
    export APACHE_RUN_GROUP=www-data
    export APACHE_PID_FILE=/var/run/apache2/apache2.pid
    export APACHE_RUN_DIR=/var/run/apache2
    export APACHE_LOG_DIR=/var/log/apache2
    mkdir -p $APACHE_RUN_DIR
    /usr/sbin/apache2 -k start
fi

echo "══════════════════════════════════════════════"
echo " ✅ SERVICES OMEGA RÉINITIALISÉS"
echo " 🔗 http://127.0.0.1:9080"
echo "══════════════════════════════════════════════"
