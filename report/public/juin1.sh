#!/bin/bash

echo "--------------------------------------------------------"
echo "🚀 DÉPLOIEMENT DE LA FLOTTE GLOBALE OMEGA SUITE 2026"
echo "--------------------------------------------------------"

# 1. Services de Base
service mariadb start
echo "✅ Base de données MariaDB : OK"

# Fonction pour lancer PHP
launch_php() {
    php -S 0.0.0.0:$1 -t /root/shared/htdocs/apachewsl2026/$2 > /dev/null 2>&1 &
    echo "  -> [Port $1] $2 : Lancé"
}

# --- SERVEUR PRINCIPAL ---
echo "🌐 Initialisation Serveur Principal..."
php -S 0.0.0.0:8080 -t /root/shared/htdocs/apachewsl2026 > /dev/null 2>&1 &
echo "  -> [Port 8080] Serveur racine (index.php) : Lancé"

# --- PÔLE FINANCE & STRATÉGIE ---
echo "📈 Initialisation Pôle Finance..."
launch_php 8094 "ingenierie"
launch_php 8095 "banque"
launch_php 8098 "assurance"
launch_php 8096 "gp"

# --- PÔLE GESTION COMMERCIALE & PME ---
echo "💼 Initialisation Pôle PME..."
launch_php 8100 "pme"
launch_php 8101 "gestion_commerciale"
launch_php 8102 "ecommerce"
launch_php 8103 "gestion_ecommerciale"
launch_php 8104 "restau"

# --- PÔLE AUTOMOBILE ---
echo "🚗 Initialisation Pôle Auto..."
launch_php 8110 "auto"
launch_php 8111 "gestion_auto"
launch_php 8112 "piece_auto"

# --- PÔLE SERVICES & RH ---
echo "👥 Initialisation Pôle Services..."
launch_php 8093 "gestion_pointage"
launch_php 8091 "gestion_ecole"
launch_php 8092 "pressing"
launch_php 8120 "clinique"

# --- PÔLE ANALYSE & SYNTHÈSE ---
echo "📊 Initialisation Pôle Reporting..."
launch_php 8130 "report/public"

# --- PREMIERS NOUVEAUX SERVICES ---
echo "🏥 Initialisation Premiers Services..."
launch_php 8140 "centrediop"
launch_php 8141 "charcuterie1"
launch_php 8142 "foot"
launch_php 8143 "librairie"
launch_php 8144 "pharmacie"

# --- VAGUES AVANCÉES ---
echo "🏨 Initialisation Applications Métier..."
launch_php 8150 "analyse_medicale"
launch_php 8151 "hotel"
launch_php 8153 "cabinet_radiologie"
launch_php 8154 "gestion_immobiliere"

# --- BOUCLE VAGUES 3 & 4 ---
for app in "portail" "couture_senegal" "genie_civil" "transit" "agence_voyage" "annuaire" "fitness" "pizzeria" "scooter" "parfumerie"; do
    PORT=$((8152 + $(echo "portail couture_senegal genie_civil transit agence_voyage annuaire fitness pizzeria scooter parfumerie" | tr ' ' '\n' | grep -n ^$app$ | cut -d: -f1) - 1))

    if [ "$app" == "portail" ]; then
        PORT=8152
    fi

    if [ -d "/root/shared/htdocs/apachewsl2026/$app" ]; then
        php -S 0.0.0.0:$PORT -t /root/shared/htdocs/apachewsl2026/$app > /dev/null 2>&1 &
        echo "  -> [Port $PORT] $app : Lancé"
    fi
done

cat << 'EOF'
--------------------------------------------------------
🌐 FLOTTE OPÉRATIONNELLE - RÉCAPITULATIF
🔗 Portail principal : http://localhost:8080

📁 FINANCE & GESTION
   - GP                : http://localhost:8097
   - Assurance         : http://localhost:8098
   - Ingénierie        : http://localhost:8094
   - Banque            : http://localhost:8095
   - PME               : http://localhost:8100
   - Gestion Comm.     : http://localhost:8101
   - E-commerce        : http://localhost:8102
   - Gestion E-comm.   : http://localhost:8103
   - Restauration      : http://localhost:8104

🚗 AUTO & SERVICES
   - Auto              : http://localhost:8110
   - Gestion Auto      : http://localhost:8111
   - Pièces Auto       : http://localhost:8112
   - Pointage          : http://localhost:8093
   - École             : http://localhost:8091
   - Pressing          : http://localhost:8092
   - Clinique          : http://localhost:8120

📊 REPORTING
   - Report (public)   : http://localhost:8130

🏥 SANTÉ & DIVERS
   - Centre DIOP       : http://localhost:8140
   - Charcuterie       : http://localhost:8141
   - Foot              : http://localhost:8142
   - Librairie         : http://localhost:8143
   - Pharmacie         : http://localhost:8144
   - Analyse Méd.      : http://localhost:8150
   - Hôtel             : http://localhost:8151
   - Cabinet Radio.    : http://localhost:8153
   - Gestion Immo.     : http://localhost:8154
--------------------------------------------------------
EOF
