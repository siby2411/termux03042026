#!/bin/bash

# Configuration de la base de données
DB_NAME="piece_auto"
DB_USER="root"
# MISE À JOUR DU MOT DE PASSE
DB_PASS="123" 
BACKUP_DIR="/mnt/e/piece_auto2026/backups"

# Vérification et création du répertoire si nécessaire
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Création du répertoire de sauvegarde: $BACKUP_DIR"
    sudo mkdir -p "$BACKUP_DIR"
fi

# Création du nom du fichier avec horodatage
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/$DB_NAME-$TIMESTAMP.sql"

echo "Démarrage de la sauvegarde de la base de données '$DB_NAME'..."

# Exécution de mysqldump
if mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_FILE"; then
    echo "Sauvegarde réussie : $BACKUP_FILE"
else
    echo "ERREUR : Échec de la sauvegarde de la base de données. Vérifiez les permissions ou le mot de passe MariaDB." >&2
    exit 1
fi

# Optionnel: Supprimer les sauvegardes de plus de 7 jours
find "$BACKUP_DIR" -type f -name "*.sql" -mtime +7 -delete

echo "Nettoyage des anciens fichiers terminé."
EOFsudo tee /var/www/piece_auto/backup.sh > /dev/null << 'EOF'
#!/bin/bash

# Configuration de la base de données
DB_NAME="piece_auto"
DB_USER="root"
# MISE À JOUR DU MOT DE PASSE
DB_PASS="123" 
BACKUP_DIR="/mnt/e/piece_auto2026/backups"

# Vérification et création du répertoire si nécessaire
if [ ! -d "$BACKUP_DIR" ]; then
    echo "Création du répertoire de sauvegarde: $BACKUP_DIR"
    sudo mkdir -p "$BACKUP_DIR"
fi

# Création du nom du fichier avec horodatage
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/$DB_NAME-$TIMESTAMP.sql"

echo "Démarrage de la sauvegarde de la base de données '$DB_NAME'..."

# Exécution de mysqldump
if mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > "$BACKUP_FILE"; then
    echo "Sauvegarde réussie : $BACKUP_FILE"
else
    echo "ERREUR : Échec de la sauvegarde de la base de données. Vérifiez les permissions ou le mot de passe MariaDB." >&2
    exit 1
fi

# Optionnel: Supprimer les sauvegardes de plus de 7 jours
find "$BACKUP_DIR" -type f -name "*.sql" -mtime +7 -delete

echo "Nettoyage des anciens fichiers terminé."
