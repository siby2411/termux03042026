#!/bin/bash
# quick_fix.sh

echo "=== CORRECTION RAPIDE SYSCOHADA ==="

echo "1. Mise à jour du mot de passe dans la table users..."
read -p "Mot de passe MySQL root: " mysql_pass

# Tester la connexion
if mysql -u root -p"$mysql_pass" -e "SELECT 1" 2>/dev/null; then
    echo "✓ Connexion MySQL réussie"
    
    # Mettre à jour en clair (plus simple pour test)
    mysql -u root -p"$mysql_pass" sysco_ohada << 'EOF'
UPDATE users SET password_hash = 'admin123' WHERE username = 'admin';
SELECT username, 
       CASE 
         WHEN password_hash = 'admin123' THEN '✓ Mot de passe mis à jour (clair)'
         ELSE password_hash
       END as status
FROM users WHERE username = 'admin';
EOF
    
    # Mettre à jour config.php
    echo "2. Mise à jour de config.php..."
    sudo sed -i "s/define('DB_PASS', '.*');/define('DB_PASS', '$mysql_pass');/" /var/www/syscoa/config.php
    
    echo "3. Redémarrage d'Apache..."
    sudo service apache2 restart
    
    echo ""
    echo "=== CORRECTION RÉUSSIE ==="
    echo "URL: http://92.168.1.33:8080/syscoa/"
    echo "Utilisateur: admin"
    echo "Mot de passe: admin123"
else
    echo "✗ Échec de la connexion MySQL"
    echo "Veuillez vérifier votre mot de passe MySQL"
fi
