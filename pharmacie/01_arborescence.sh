#!/bin/bash
# ============================================================
# SCRIPT 01 — ARBORESCENCE COMPLÈTE
# Pharmacie + Revendeur Médical
# Environnement : Termux / proot-distro / MariaDB / PHP
# Auteur : Directeur Projet — Ingénierie Logicielle Sénégal
# Date   : 2026
# ============================================================

BASE="$HOME/shared/htdocs/apachewsl2026"
cd "$BASE" || exit 1

# ─────────────────────────────────────────────
# 1. ARBORESCENCE — APPLICATION PHARMACIE
# ─────────────────────────────────────────────
mkdir -p pharmacie/{config,core,modules/{medicaments,stock,ventes,ordonnances,fournisseurs,clients,caisse,rapports,utilisateurs},assets/{css,js,img,fonts},templates/{partials,layouts},api,logs,uploads/{ordonnances,documents},install}

# ─────────────────────────────────────────────
# 2. ARBORESCENCE — APPLICATION REVENDEUR MÉDICAL
# ─────────────────────────────────────────────
mkdir -p revendeur_medical/{config,core,modules/{produits,stock,clients,fournisseurs,commandes,devis,facturation,livraisons,sav,rapports,utilisateurs},assets/{css,js,img,fonts},templates/{partials,layouts},api,logs,uploads/{produits,documents,contrats},install}

# ─────────────────────────────────────────────
# 3. RÉPERTOIRE COMMUN PARTAGÉ (bibliothèques)
# ─────────────────────────────────────────────
mkdir -p shared_libs/{vendor,helpers,classes,pdf_generator,barcode,sms_gateway}

echo "✅ Arborescence créée avec succès"
tree "$BASE/pharmacie" 2>/dev/null || find "$BASE/pharmacie" -type d | sort
tree "$BASE/revendeur_medical" 2>/dev/null || find "$BASE/revendeur_medical" -type d | sort
