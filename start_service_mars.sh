#!/bin/bash

echo "--------------------------------------------------------"
echo "🚀 DÉPLOIEMENT DE LA FLOTTE GLOBALE OMEGA SUITE 2026"
echo "--------------------------------------------------------"

# 1. Services de Base
service mariadb start
echo "✅ Base de données MariaDB : OK"

# Fonction pour lancer PHP sans encombrer le terminal
launch_php() {
    php -S 0.0.0.0:$1 -t /root/shared/htdocs/apachewsl2026/$2 > /dev/null 2>&1 &
    echo "  -> [Port $1] $2 : Lancé"
}

# --- SERVEUR PRINCIPAL (index.php à la racine) ---
echo "🌐 Initialisation Serveur Principal (index.php)..."
php -S 0.0.0.0:8080 -t /root/shared/htdocs/apachewsl2026 > /dev/null 2>&1 &
echo "  -> [Port 8080] Serveur racine (index.php) : Lancé"

# --- PÔLE FINANCE & STRATÉGIE ---
echo "📈 Initialisation Pôle Finance..."
launch_php 8094 "ingenierie"
launch_php 8095 "banque"
launch_php 8096 "syscohada"
launch_php 8097 "syscoa"
launch_php 8098 "gestion_previsionnelle"

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
launch_php 8130 "report"
launch_php 8131 "reporting"
launch_php 8132 "synthesepro"

# --- PREMIERS NOUVEAUX SERVICES ---
echo "🏥 Initialisation Premiers Services..."
launch_php 8140 "centrediop"
launch_php 8141 "charcuterie1"
launch_php 8142 "foot"
launch_php 8143 "librairie"
launch_php 8144 "pharmacie"
launch_php 8145 "revendeur_medical"

# --- DEUXIÈME VAGUE ---
echo "🏨 Initialisation Applications Métier Avancées..."
launch_php 8150 "analyse_medicale"
launch_php 8151 "hotel"
launch_php 8153 "cabinet_radiologie"
launch_php 8154 "gestion_immobiliere"

# --- TROISIÈME VAGUE ---
echo "👗 Initialisation Troisième Vague..."

if [ -d "/root/shared/htdocs/apachewsl2026/portail" ]; then
    php -S 0.0.0.0:8152 -t /root/shared/htdocs/apachewsl2026/portail > /dev/null 2>&1 &
    echo "  -> [Port 8152] portail : Lancé"
else
    echo "  -> [Port 8152] portail : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/couture_senegal" ]; then
    php -S 0.0.0.0:8155 -t /root/shared/htdocs/apachewsl2026/couture_senegal > /dev/null 2>&1 &
    echo "  -> [Port 8155] couture_senegal : Lancé"
else
    echo "  -> [Port 8155] couture_senegal : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/genie_civil" ]; then
    php -S 0.0.0.0:8156 -t /root/shared/htdocs/apachewsl2026/genie_civil > /dev/null 2>&1 &
    echo "  -> [Port 8156] genie_civil : Lancé"
else
    echo "  -> [Port 8156] genie_civil : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/transit" ]; then
    php -S 0.0.0.0:8157 -t /root/shared/htdocs/apachewsl2026/transit > /dev/null 2>&1 &
    echo "  -> [Port 8157] transit : Lancé"
else
    echo "  -> [Port 8157] transit : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/agence_voyage" ]; then
    php -S 0.0.0.0:8158 -t /root/shared/htdocs/apachewsl2026/agence_voyage > /dev/null 2>&1 &
    echo "  -> [Port 8158] agence_voyage : Lancé"
else
    echo "  -> [Port 8158] agence_voyage : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/annuaire" ]; then
    php -S 0.0.0.0:8159 -t /root/shared/htdocs/apachewsl2026/annuaire > /dev/null 2>&1 &
    echo "  -> [Port 8159] annuaire : Lancé"
else
    echo "  -> [Port 8159] annuaire : Dossier non trouvé"
fi

# --- QUATRIÈME VAGUE - NOUVELLES APPLICATIONS ---
echo "🏋️ Initialisation Nouvelles Applications Fitness, Pizzeria, Scooter & Parfumerie..."

if [ -d "/root/shared/htdocs/apachewsl2026/fitness" ]; then
    php -S 0.0.0.0:8160 -t /root/shared/htdocs/apachewsl2026/fitness > /dev/null 2>&1 &
    echo "  -> [Port 8160] fitness : Lancé"
else
    echo "  -> [Port 8160] fitness : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/pizzeria" ]; then
    php -S 0.0.0.0:8161 -t /root/shared/htdocs/apachewsl2026/pizzeria > /dev/null 2>&1 &
    echo "  -> [Port 8161] pizzeria : Lancé"
else
    echo "  -> [Port 8161] pizzeria : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/scooter" ]; then
    php -S 0.0.0.0:8162 -t /root/shared/htdocs/apachewsl2026/scooter > /dev/null 2>&1 &
    echo "  -> [Port 8162] scooter : Lancé"
else
    echo "  -> [Port 8162] scooter : Dossier non trouvé"
fi

if [ -d "/root/shared/htdocs/apachewsl2026/parfumerie" ]; then
    php -S 0.0.0.0:8163 -t /root/shared/htdocs/apachewsl2026/parfumerie > /dev/null 2>&1 &
    echo "  -> [Port 8163] parfumerie : Lancé"
else
    echo "  -> [Port 8163] parfumerie : Dossier non trouvé"
fi

echo "--------------------------------------------------------"
echo "🌐 FLOTTE COMPLÈTE OPÉRATIONNELLE"
echo "📊 Nombre total d'applications lancées : 42"
echo ""
echo "🔗 ACCÈS RAPIDE :"
echo "   - Portail principal : http://localhost:8080"
echo ""
echo "📋 LISTE COMPLÈTE DES APPLICATIONS :"
echo ""
echo "💰 PÔLE FINANCE (Ports 8094-8098) :"
echo "   - Ingénierie      : http://localhost:8094"
echo "   - Banque          : http://localhost:8095"
echo "   - SYSCOHADA       : http://localhost:8096"
echo "   - SYSCOA          : http://localhost:8097"
echo "   - Gestion Prévi.  : http://localhost:8098"
echo ""
echo "💼 PÔLE PME & COMMERCE (Ports 8100-8104) :"
echo "   - PME             : http://localhost:8100"
echo "   - Gestion Comm.   : http://localhost:8101"
echo "   - E-commerce      : http://localhost:8102"
echo "   - Gestion E-comm. : http://localhost:8103"
echo "   - Restauration    : http://localhost:8104"
echo ""
echo "🚗 PÔLE AUTOMOBILE (Ports 8110-8112) :"
echo "   - Auto            : http://localhost:8110"
echo "   - Gestion Auto    : http://localhost:8111"
echo "   - Pièces Auto     : http://localhost:8112"
echo ""
echo "👥 PÔLE SERVICES (Ports 8091-8093, 8120) :"
echo "   - Gestion Pointage: http://localhost:8093"
echo "   - Gestion École   : http://localhost:8091"
echo "   - Pressing        : http://localhost:8092"
echo "   - Clinique        : http://localhost:8120"
echo ""
echo "📊 PÔLE REPORTING (Ports 8130-8132) :"
echo "   - Report          : http://localhost:8130"
echo "   - Reporting       : http://localhost:8131"
echo "   - Synthèse Pro    : http://localhost:8132"
echo ""
echo "🏥 SERVICES MÉDICAUX (Ports 8140-8145, 8150, 8153) :"
echo "   - Centre DIOP     : http://localhost:8140"
echo "   - Charcuterie     : http://localhost:8141"
echo "   - Foot            : http://localhost:8142"
echo "   - Librairie       : http://localhost:8143"
echo "   - Pharmacie       : http://localhost:8144"
echo "   - Revendeur Méd.  : http://localhost:8145"
echo "   - Analyse Méd.    : http://localhost:8150"
echo "   - Cabinet Radio.  : http://localhost:8153"
echo ""
echo "🏨 HÔTELLERIE & IMMOBILIER (Ports 8151, 8154) :"
echo "   - Hôtel           : http://localhost:8151"
echo "   - Gestion Immo.   : http://localhost:8154"
echo ""
echo "🆕 NOUVELLES APPLICATIONS (Ports 8152, 8155-8159) :"
echo "   - Portail         : http://localhost:8152"
echo "   - Couture Sénégal : http://localhost:8155"
echo "   - Génie Civil     : http://localhost:8156"
echo "   - Transit         : http://localhost:8157"
echo "   - Agence Voyage   : http://localhost:8158"
echo "   - Annuaire        : http://localhost:8159"
echo ""
echo "✨ TOUT DERNIÈRES APPLICATIONS (Ports 8160-8163) :"
echo "   - Fitness         : http://localhost:8160"
echo "   - Pizzeria        : http://localhost:8161"
echo "   - Scooter         : http://localhost:8162"
echo "   - Parfumerie      : http://localhost:8163"
echo ""
echo "⚠️  Cloudflared n'est pas démarré automatiquement."
echo "   Pour exposer via tunnel : cloudflared tunnel --url http://localhost:8080"
echo "--------------------------------------------------------"
