
## Exécution de la sauvegarde :

```bash
# Exécuter le script de sauvegarde
bash /tmp/backup_script.sh

# Ou si vous préférez exécuter étape par étape, voici une version simplifiée :
echo "🚀 Démarrage de la sauvegarde complète..."

# Créer le répertoire de sauvegarde
BACKUP_DIR="/home/momo/syscohada_full_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# 1. Sauvegarde de /var/www
echo "📦 Sauvegarde de /var/www..."
sudo tar -czf "$BACKUP_DIR/var_www_backup.tar.gz" -C /var www

# 2. Sauvegarde de toutes les bases de données
echo "🗃️ Sauvegarde des bases de données..."
DATABASES=$(mysql -u root -p123 -e "SHOW DATABASES;" | grep -Ev "(Database|information_schema|performance_schema|mysql|sys)")

for DB in $DATABASES; do
    echo "  - $DB"
    mysqldump -u root -p123 --routines --triggers --events "$DB" | gzip > "$BACKUP_DIR/${DB}_backup.sql.gz"
done

# 3. Créer un fichier d'informations
echo "Sauvegarde créée le: $(date)" > "$BACKUP_DIR/INFO.txt"
echo "Bases de données sauvegardées:" >> "$BACKUP_DIR/INFO.txt"
for DB in $DATABASES; do
    echo "- $DB" >> "$BACKUP_DIR/INFO.txt"
done

# 4. Compresser tout
cd "$BACKUP_DIR/.."
tar -czf "${BACKUP_DIR##*/}_final.tar.gz" "${BACKUP_DIR##*/}"

echo "✅ Sauvegarde terminée: ${BACKUP_DIR}_final.tar.gz"
echo "📊 Taille: $(du -h ${BACKUP_DIR}_final.tar.gz | cut -f1)"
