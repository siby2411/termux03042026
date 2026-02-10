# Créer le script avec les droits d'administration
sudo bash -c 'cat > /usr/local/bin/check_backup_integrity.sh << EOF
#!/bin/bash
LOG_FILE="/var/log/syscohada_backup.log"
ALERT_DAYS=2

# Vérifier la dernière sauvegarde
last_backup=$(find /mnt/e/decembreohada2026/database -name "*.sql.gz" -type f -mtime -$ALERT_DAYS | head -1)

if [ -z "$last_backup" ]; then
    echo "ALERTE: Aucune sauvegarde depuis $ALERT_DAYS jours!" | \
    mail -s "ALERTE Sauvegarde SyscoHADA" admin@entreprise.com
    exit 1
fi

# Vérifier la taille (doit être > 1MB)
size=$(stat -c%s "$last_backup" 2>/dev/null)
if [ "$size" -lt 1048576 ]; then
    echo "ALERTE: Sauvegarde trop petite!" | \
    mail -s "ALERTE Sauvegarde SyscoHADA" admin@entreprise.com
fi
EOF'

# Rendre le script exécutable
sudo chmod +x /usr/local/bin/check_backup_integrity.sh
