#!/bin/bash
# Script de sauvegarde du projet Centre de Santé Mamadou Diop
# Version pour MariaDB sans mot de passe

set -e

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions d'affichage
print_step() {
    echo -e "\n${BLUE}═══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Date et heure pour le nom du dossier
DATE_HEURE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/sdcard/centrediop/backup_$DATE_HEURE"
PROJECT_DIR="/root/shared/htdocs/apachewsl2026/centrediop"
DB_NAME="centrediop"

clear
echo -e "${BLUE}"
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                                                              ║"
echo "║   SAUVEGARDE DU CENTRE DE SANTÉ MAMADOU DIOP                ║"
echo "║                                                              ║"
echo "║   Date: $(date)                   ║"
echo "║                                                              ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Vérifier que le répertoire du projet existe
print_step "VÉRIFICATION DU RÉPERTOIRE PROJET"
if [ -d "$PROJECT_DIR" ]; then
    print_success "Répertoire projet trouvé: $PROJECT_DIR"
else
    print_error "Répertoire projet non trouvé: $PROJECT_DIR"
    exit 1
fi

# Vérifier que MariaDB est accessible
print_step "VÉRIFICATION DE MARIA DB"
if command -v mariadb &> /dev/null; then
    print_success "MariaDB est installé"
    
    # Tester la connexion sans mot de passe
    if mariadb -u root -e "USE $DB_NAME" 2>/dev/null; then
        print_success "Connexion à la base '$DB_NAME' réussie"
    else
        print_warning "Impossible de se connecter à la base '$DB_NAME'"
        echo "   Vérifiez que la base existe: mariadb -u root -e 'CREATE DATABASE IF NOT EXISTS $DB_NAME;'"
    fi
else
    print_error "MariaDB n'est pas installé"
    exit 1
fi

# Créer le répertoire de sauvegarde
print_step "CRÉATION DU RÉPERTOIRE DE SAUVEGARDE"
mkdir -p "$BACKUP_DIR"
print_success "Répertoire créé: $BACKUP_DIR"

# Sauvegarde des fichiers du projet
print_step "SAUVEGARDE DES FICHIERS DU PROJET"

echo "📁 Copie des fichiers PHP et configurations..."
# Copier les fichiers à la racine
cp "$PROJECT_DIR"/*.php "$BACKUP_DIR/" 2>/dev/null || true
cp "$PROJECT_DIR"/*.sql "$BACKUP_DIR/" 2>/dev/null || true
cp "$PROJECT_DIR"/*.sh "$BACKUP_DIR/" 2>/dev/null || true
print_success "Fichiers racine copiés"

# Sauvegarde des dossiers importants
echo "📁 Sauvegarde des dossiers modules, includes, config, assets..."
mkdir -p "$BACKUP_DIR/modules" "$BACKUP_DIR/includes" "$BACKUP_DIR/config" "$BACKUP_DIR/assets"

if [ -d "$PROJECT_DIR/modules" ]; then
    cp -r "$PROJECT_DIR/modules"/* "$BACKUP_DIR/modules/" 2>/dev/null || true
    print_success "Dossier modules sauvegardé"
fi

if [ -d "$PROJECT_DIR/includes" ]; then
    cp -r "$PROJECT_DIR/includes"/* "$BACKUP_DIR/includes/" 2>/dev/null || true
    print_success "Dossier includes sauvegardé"
fi

if [ -d "$PROJECT_DIR/config" ]; then
    cp -r "$PROJECT_DIR/config"/* "$BACKUP_DIR/config/" 2>/dev/null || true
    print_success "Dossier config sauvegardé"
fi

if [ -d "$PROJECT_DIR/assets" ]; then
    cp -r "$PROJECT_DIR/assets"/* "$BACKUP_DIR/assets/" 2>/dev/null || true
    print_success "Dossier assets sauvegardé"
fi

# Sauvegarde de la base de données
print_step "SAUVEGARDE DE LA BASE DE DONNÉES"

# Sauvegarde de la structure et des données
DB_BACKUP_FILE="$BACKUP_DIR/centrediop_$DATE_HEURE.sql"
echo "📦 Sauvegarde de la base '$DB_NAME' vers $DB_BACKUP_FILE"

if mariadb-dump -u root "$DB_NAME" > "$DB_BACKUP_FILE" 2>/dev/null; then
    print_success "Base de données sauvegardée avec succès"
    
    # Taille du fichier
    DB_SIZE=$(du -h "$DB_BACKUP_FILE" | cut -f1)
    echo "   Taille: $DB_SIZE"
else
    print_error "Erreur lors de la sauvegarde de la base de données"
    echo "   Commande essayée: mariadb-dump -u root $DB_NAME"
fi

# Sauvegarde de la structure seule (optionnel)
DB_STRUCTURE_FILE="$BACKUP_DIR/centrediop_structure_$DATE_HEURE.sql"
echo "📦 Sauvegarde de la structure uniquement..."

if mariadb-dump -u root --no-data "$DB_NAME" > "$DB_STRUCTURE_FILE" 2>/dev/null; then
    print_success "Structure de la base sauvegardée"
fi

# Sauvegarde des données seules
DB_DATA_FILE="$BACKUP_DIR/centrediop_data_$DATE_HEURE.sql"
echo "📦 Sauvegarde des données uniquement..."

if mariadb-dump -u root --no-create-info "$DB_NAME" > "$DB_DATA_FILE" 2>/dev/null; then
    print_success "Données de la base sauvegardées"
fi

# Création d'un fichier README avec les informations
print_step "CRÉATION DU FICHIER README"

cat > "$BACKUP_DIR/README.txt" << EOF
==================================================
SAUVEGARDE DU CENTRE DE SANTÉ MAMADOU DIOP
==================================================

Date de la sauvegarde: $(date)
Dossier de sauvegarde: $BACKUP_DIR

CONTENU DE LA SAUVEGARDE:
-------------------------
1. Fichiers du projet PHP
2. Base de données complète (centrediop_$DATE_HEURE.sql)
3. Structure de la base (centrediop_structure_$DATE_HEURE.sql)
4. Données seules (centrediop_data_$DATE_HEURE.sql)

INFORMATIONS DE CONNEXION:
-------------------------
Base de données: centrediop
Utilisateur: root (pas de mot de passe)
Interface: http://127.0.0.1:8000

COMPTES UTILISATEURS:
--------------------
Admin      : admin / admin123
Médecins   : dr.fall / pediatre123
           dr.diop / medecin123
           dr.ndiaye / medecin123
           dr.seck / medecin123
           dr.ba / medecin123
Sage-femme : sagefemme1 / sagefemme123
Caissier   : caissier1 / caissier123

FICHIERS DE SAUVEGARDE:
----------------------
- centrediop_$DATE_HEURE.sql : Base de données complète
- centrediop_structure_$DATE_HEURE.sql : Structure uniquement
- centrediop_data_$DATE_HEURE.sql : Données uniquement

RESTAURATION:
------------
1. Base de données complète:
   mariadb -u root centrediop < centrediop_$DATE_HEURE.sql

2. Structure uniquement:
   mariadb -u root centrediop < centrediop_structure_$DATE_HEURE.sql

3. Données uniquement:
   mariadb -u root centrediop < centrediop_data_$DATE_HEURE.sql

4. Fichiers: 
   Copier le contenu vers $PROJECT_DIR

5. Serveur:
   cd $PROJECT_DIR
   php -S 0.0.0.0:8000

==================================================
