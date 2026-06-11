#!/bin/bash

# 1. Redémarrage du service MariaDB pour garantir la disponibilité
echo "Redémarrage du service MariaDB..."
service mariadb restart > /dev/null 2>&1

# Vérification du succès du redémarrage
if [ $? -eq 0 ]; then
    echo "MariaDB redémarré avec succès."
else
    echo "Erreur lors du redémarrage de MariaDB."
    exit 1
fi

# 2. Configuration de la connexion
MYSQL_CMD="mysql -u root --socket=/var/run/mysqld/mysqld.sock"

# 3. Récupération de la liste des bases de données
DATABASES=($($MYSQL_CMD -N -e "SHOW DATABASES WHERE Database NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys');"))

# 4. Menu interactif
echo "--- Sélectionner une base de données ---"
select DB in "${DATABASES[@]}"; do
    if [ -n "$DB" ]; then
        echo "Connexion à : $DB ..."
        $MYSQL_CMD "$DB"
        break
    else
        echo "Choix invalide. Veuillez réessayer."
    fi
done
