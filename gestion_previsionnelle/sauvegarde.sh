#!/bin/bash

# Configuration
DB_NAME="ingenierie"
DB_USER="root" # À remplacer par l'utilisateur MariaDB sécurisé si différent
DB_PASSWORD="123" # À remplacer par le mot de passe réel
PROJECT_PATH="/var/www/gestion_previsionnelle"
BACKUP_DIR="/mnt/e/gestionprevisionnelle2026"
DATE_TIME=$(date +%Y%m%d_%H%M%S)

# 1. Créer le répertoire de sauvegarde s'il n'existe pas
mkdir -p "$BACKUP_DIR"

# Vérifier si le répertoire cible existe après création/vérification
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Erreur : Le répertoire de sauvegarde $BACKUP_DIR n'existe pas ou ne peut pas être créé."
    exit 1
fi

echo "--- Démarrage de la sauvegarde à $DATE_TIME ---"

# 2. Sauvegarde de la base de données MariaDB
DB_BACKUP_FILE="$BACKUP_DIR/$DB_NAME-$DATE_TIME.sql"
echo "Sauvegarde de la base de données $DB_NAME vers $DB_BACKUP_FILE..."

if mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$DB_BACKUP_FILE"; then
    echo "Base de données sauvegardée avec succès."
else
    echo "Erreur lors de la sauvegarde de la base de données."
fi

# 3. Sauvegarde et compression du répertoire du projet
PROJECT_ARCHIVE="$BACKUP_DIR/projet_gestion_previsionnelle-$DATE_TIME.tar.gz"
echo "Archivage du projet $PROJECT_PATH vers $PROJECT_ARCHIVE..."

if tar -czf "$PROJECT_ARCHIVE" -C "$(dirname "$PROJECT_PATH")" "$(basename "$PROJECT_PATH")"; then
    echo "Projet archivé et compressé avec succès."
else
    echo "Erreur lors de l'archivage du projet."
fi

echo "--- Sauvegarde terminée. Les fichiers sont dans $BACKUP_DIR ---"

# 4. Définir les permissions (pour s'assurer que vous pouvez y accéder)
chmod -R 755 "$BACKUP_DIR"
