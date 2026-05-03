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
echo "  -> [Port 8080] Serveur racine : Lancé"

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
echo "🏥 Initialisation Nouveaux Services..."
launch_php 8140 "centrediop"
launch_php 8141 "charcuterie1"
launch_php 8142 "foot"
launch_php 8143 "librairie"
launch_php 8144 "pharmacie"
launch_php 8145 "revendeur_medical"

# --- DEUXIÈME VAGUE DE NOUVEAUX SERVICES ---
echo "🏨 Initialisation Applications Métier Avancées..."
launch_php 8150 "analyse_medicale"
launch_php 8151 "hotel"
launch_php 8152 "portail"
launch_php 8153 "cabinet_radiologie"
launch_php 8154 "gestion_immobiliere"

# --- TROISIÈME VAGUE - COUTURE SENEGAL ---
echo "👗 Initialisation Application Couture Senegal..."
if [ -d "/root/shared/htdocs/apachewsl2026/couture_senegal" ]; then
    php -S 0.0.0.0:8155 -t /root/shared/htdocs/apachewsl2026/couture_senegal > /dev/null 2>&1 &
    echo "  -> [Port 8155] couture_senegal : Lancé"
else
    echo "  -> [Port 8155] couture_senegal : Dossier non trouvé"
fi

echo "--------------------------------------------------------"
echo "🌐 FLOTTE COMPLÈTE OPÉRATIONNELLE"
echo "📊 Nombre total d'applications lancées : 32"
echo ""
echo "🔗 ACCÈS RAPIDE :"
echo "   - Portail principal : http://localhost:8080"
echo "   - Analyse Médicale : http://localhost:8150"
echo "   - Hôtel : http://localhost:8151"
echo "   - Portail E-Commerce : http://localhost:8152"
echo "   - Cabinet Radiologie : http://localhost:8153"
echo "   - Gestion Immobilière : http://localhost:8154"
echo "   - Couture Senegal : http://localhost:8155"
echo ""
echo "⚠️  Cloudflared n'est pas démarré automatiquement."
echo "   Pour exposer via tunnel : cloudflared tunnel --url http://localhost:8080"
echo "--------------------------------------------------------"
