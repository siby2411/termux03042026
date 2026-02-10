#!/bin/bash

# Configuration
BACKUP_ROOT="/mnt/e/sauvegardes_web"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$BACKUP_ROOT/sauvegarde_$DATE"
DB_BACKUP_DIR="$BACKUP_DIR/bases_donnees"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Fonction de log
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERREUR]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[ATTENTION]${NC} $1"
}

# Création des répertoires
log "Création du répertoire de sauvegarde: $BACKUP_DIR"
mkdir -p $DB_BACKUP_DIR

# Vérification de l'espace disque
log "Vérification de l'espace disque..."
df -h /mnt/e

# === SAUVEGARDE DES PROJETS WEB ===
log "Début de la sauvegarde des projets web..."

# Sauvegarde complète de /var/www
log "Sauvegarde complète de /var/www..."
tar -czf $BACKUP_DIR/projets_web_complet.tar.gz -C /var/www . 2>/dev/null

# Sauvegarde individuelle de chaque projet
cd /var/www
for projet in */; do
    if [ -d "$projet" ]; then
        projet_nom=$(basename "$projet")
        log "Sauvegarde du projet: $projet_nom"
        tar -czf $BACKUP_DIR/projet_${projet_nom}.tar.gz "$projet_nom" 2>/dev/null
        
        # Vérification de la sauvegarde
        if [ $? -eq 0 ]; then
            log "✓ $projet_nom sauvegardé avec succès"
        else
            error "Échec de la sauvegarde de $projet_nom"
        fi
    fi
done

# === SAUVEGARDE DES BASES DE DONNÉES ===
log "Début de la sauvegarde des bases de données MySQL..."

# Demander le mot de passe MySQL une seule fois
echo -n "Entrez le mot de passe MySQL root: "
read -s MYSQL_PASSWORD
echo

# Obtenir la liste des bases de données
DATABASES=$(mysql -u root -p$MYSQL_PASSWORD -e "SHOW DATABASES;" 2>/dev/null | grep -v Database | grep -v -E '(information_schema|performance_schema|mysql|sys)')

if [ -z "$DATABASES" ]; then
    error "Aucune base de données trouvée ou erreur de connexion MySQL"
    exit 1
fi

# Sauvegarder chaque base de données
for DB in $DATABASES; do
    log "Sauvegarde de la base: $DB"
    mysqldump -u root -p$MYSQL_PASSWORD --opt --routines --triggers --events $DB 2>/dev/null > $DB_BACKUP_DIR/${DB}.sql
    
    if [ $? -eq 0 ]; then
        log "✓ Base $DB sauvegardée ($(du -h $DB_BACKUP_DIR/${DB}.sql | cut -f1))"
    else
        error "Échec de la sauvegarde de $DB"
    fi
done

# Sauvegarde complète de toutes les bases
log "Création de la sauvegarde complète..."
mysqldump -u root -p$MYSQL_PASSWORD --all-databases 2>/dev/null > $DB_BACKUP_DIR/all_databases_complete.sql

# === CRÉATION DES ARCHIVES ===
log "Création des archives finales..."

# Compresser les bases de données
tar -czf $BACKUP_DIR/bases_donnees_complete.tar.gz -C $DB_BACKUP_DIR . 2>/dev/null

# Créer un fichier README avec les informations
cat > $BACKUP_DIR/README.txt << EOF
SAUVEGARDE COMPLÈTE - $(date)
================================

Projets sauvegardés:
$(ls -la /var/www/)

Bases de données sauvegardées:
$DATABASES

Emplacement: $BACKUP_DIR
Taille totale: $(du -sh $BACKUP_DIR | cut -f1)

Procédure de restauration:
1. Extraire les archives .tar.gz
2. Pour les projets: copier dans /var/www/
3. Pour les bases: mysql -u root -p < fichier.sql

EOF

# === RAPPORT FINAL ===
log "=== SAUVEGARDE TERMINÉE ==="
log "Emplacement: $BACKUP_DIR"
log "Taille totale: $(du -sh $BACKUP_DIR | cut -f1)"
log "Projets sauvegardés: $(ls /var/www/ | wc -l)"
log "Bases de données: $(echo "$DATABASES" | wc -l)"
log "============================"

# Nettoyer le mot de passe de la mémoire
unset MYSQL_PASSWORD
