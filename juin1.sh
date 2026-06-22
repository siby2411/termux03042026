#!/bin/bash

echo "🚀 OMEGA SUITE 2026"

service mariadb start

nohup php -S 0.0.0.0:8080 -t /root/shared/htdocs/apachewsl2026 > /dev/null 2>&1 &

launch_php() {
    if [[ "$2" == "piece_auto" || "$2" == "report" ]]; then
        TARGET_DIR="/root/shared/htdocs/apachewsl2026/$2/public"
    else
        TARGET_DIR="/root/shared/htdocs/apachewsl2026/$2"
    fi

    php -S 0.0.0.0:$1 -t "$TARGET_DIR" > /dev/null 2>&1 &
    echo "[$1] $2 lancé"
}

# ===================== CORE =====================
launch_php 8094 "ingenierie"
launch_php 8095 "banque"
launch_php 8098 "assurance"
launch_php 8096 "gp"

# ===================== BUSINESS =====================
launch_php 8100 "pme"
launch_php 8101 "gestion_commerciale"

# ===================== COUTURE =====================
launch_php 8102 "couture_senegal"

# ===================== DIGITAL =====================
launch_php 8103 "ecommerce"
launch_php 8091 "gestion_ecole"

# ===================== FIN =====================
echo "🌐 OMEGA SUITE READY"
