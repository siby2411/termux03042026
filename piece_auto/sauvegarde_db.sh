#!/bin/bash

# Configuration
DB_NAME="piece_auto"
BACKUP_DIR="/mnt/e/piece_autol6decembre2025" # Répertoire de destination sur E:
SCRIPT_PATH="/var/www/piece_auto/sauvegarde_db.sh"

# Création du nom de fichier avec horodatage
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/sauvegarde_${DB_NAME}_$TIMESTAMP.sql"

echo "--- Script de Sauvegarde de la Base de Données ---"

# 1. Vérifier si le répertoire de sauvegarde existe.
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Le répertoire de sauvegarde $BACKUP_DIR n'existe pas."
    echo "Tentative de création du répertoire..."
    # Utilisation de 'sudo' pour garantir les permissions d'écriture sur le point de montage /mnt/e
    sudo mkdir -p "$BACKUP_DIR" 
    if [ $? -ne 0 ]; then
        echo "Erreur : Impossible de créer le répertoire de sauvegarde. Veuillez vérifier le montage de /mnt/e."
        exit 1
    fi
    echo "Répertoire créé avec succès."
fi

# 2. Demande du mot de passe MySQL root
echo "Authentification requise pour mysqldump."
read -s -p "Veuillez entrer le mot de passe de l'utilisateur root MySQL : " MYSQL_ROOT_PASSWORD
echo

# 3. Exécution de mysqldump
echo "Début de la sauvegarde de la base de données '$DB_NAME' vers $BACKUP_FILE..."

if mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" "$DB_NAME" > "$BACKUP_FILE"; then
    echo "Sauvegarde terminée avec succès : $BACKUP_FILE"
    
    # 4. Vérification de la taille du fichier
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "Taille du fichier de sauvegarde : $FILE_SIZE"
else
    echo "Erreur lors de l'exécution de mysqldump."
    echo "Vérifiez que le mot de passe root et les permissions sont corrects."
    rm -f "$BACKUP_FILE" # Supprime le fichier partiel en cas d'échec
    exit 1
fi

echo "--- Fin du Script ---"
