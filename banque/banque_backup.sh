#!/bin/bash

# ==========================================================
# Script de Sauvegarde de la Banque et de la Base de Données
# ==========================================================

# --- 1. CONFIGURATION (Identique) ---

PROJECT_DIR="/var/www/banque"
BACKUP_DEST="/mnt/e/banque2026"
DB_NAME="banque"
DB_USER="root"
DB_PASS="123" 
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
SQL_FILE="${DB_NAME}_${TIMESTAMP}.sql"
PROJECT_ARCHIVE_FILE="project_banque_${TIMESTAMP}.tar.gz"

# --- 2. VÉRIFICATION DU RÉPERTOIRE DE DESTINATION (Identique) ---

if [ ! -d "$BACKUP_DEST" ]; then
  echo "Le répertoire de destination $BACKUP_DEST n'existe pas. Tentative de création..."
  mkdir -p "$BACKUP_DEST"
  if [ $? -ne 0 ]; then
    echo "Erreur : Impossible de créer le répertoire de sauvegarde. Script annulé."
    exit 1
  fi
fi

# --- 3. SAUVEGARDE DE LA BASE DE DONNÉES (Identique) ---

echo "Début de la sauvegarde de la base de données '$DB_NAME'..."
mysqldump -u$DB_USER -p$DB_PASS --single-transaction $DB_NAME > "$BACKUP_DEST/$SQL_FILE"

if [ $? -ne 0 ]; then
  echo "❌ ERREUR : La sauvegarde de la base de données a échoué."
  exit 1
else
  echo "✅ Base de données sauvegardée avec succès dans $SQL_FILE"
fi

# --- 4. SAUVEGARDE DES FICHIERS DU PROJET (CORRECTION ICI) ---

echo "Début de la compression des fichiers du projet..."

# Ancienne commande (faisant erreur) :
# tar -czf "$BACKUP_DEST/$PROJECT_ARCHIVE_FILE" -C "$PROJECT_DIR" . --exclude="$BACKUP_DEST" --exclude="*.sql"

# Nouvelle commande (Correction) :
# Les options --exclude doivent être placées avant le répertoire source (.), et l'argument -C
# permet de se placer dans PROJECT_DIR avant de commencer l'archivage.

tar -czf "$BACKUP_DEST/$PROJECT_ARCHIVE_FILE" \
    -C "$PROJECT_DIR" \
    --exclude="banque_backup.sh" \
    --exclude="$BACKUP_DEST" \
    --exclude="*.sql" \
    . 

if [ $? -eq 0 ]; then
  echo "✅ Fichiers du projet sauvegardés avec succès dans $PROJECT_ARCHIVE_FILE"
else
  echo "❌ ERREUR : La compression des fichiers du projet a échoué."
  exit 1
fi

# --- 5. FIN DU SCRIPT (Identique) ---

echo "Sauvegarde complète terminée à : $(date)"
echo "Les fichiers sont disponibles dans : $BACKUP_DEST"
