 #!/bin/bash
# verify_installation_wsl.sh

echo "=== VÉRIFICATION FINALE DE L'INSTALLATION SYSCOHADA (WSL) ==="

echo "1. Vérification de la connexion MySQL..."
if mysql -u root -p123 -e "SELECT 1" 2>/dev/null; then
    echo "✅ Connexion MySQL OK"
else
    echo "❌ Problème de connexion MySQL"
    exit 1
fi

echo "2. Vérification de l'utilisateur admin..."
mysql -u root -p123 sysco_ohada << 'EOF'
SELECT 
    username,
    password_hash,
    CASE 
        WHEN password_hash = 'admin123' THEN '✅ Mot de passe en clair correct'
        WHEN password_hash LIKE '$2y$10$%' THEN '✅ Mot de passe hashé'
        ELSE '❌ Format de mot de passe inconnu'
    END as status
FROM users 
WHERE username = 'admin';
EOF

echo "3. Vérification du fichier config.php..."
if sudo grep -q "define('DB_PASS', '123');" /var/www/syscoa/config.php; then
    echo "✅ config.php a le bon mot de passe MySQL"
else
    echo "❌ config.php n'a pas le bon mot de passe MySQL"
    sudo grep "DB_PASS" /var/www/syscoa/config.php
fi

echo "4. Vérification du service Apache (WSL)..."
if ps aux | grep -v grep | grep apache2; then
    echo "✅ Apache est en cours d'exécution (WSL)"
else
    echo "❌ Apache n'est pas en cours d'exécution"
    sudo service apache2 status
fi

echo "5. Vérification des permissions..."
if [ -r "/var/www/syscoa/login.php" ] && [ -r "/var/www/syscoa/config.php" ]; then
    echo "✅ Les fichiers sont accessibles"
else
    echo "❌ Problème de permissions"
    sudo ls -la /var/www/syscoa/*.php
fi

echo "6. Test de la page de login via curl..."
response_code=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/syscoa/login.php)
if [ "$response_code" = "200" ]; then
    echo "✅ Page de login accessible (HTTP 200)"
else
    echo "❌ Page de login non accessible (HTTP $response_code)"
fi

echo ""
echo "=== RÉSUMÉ DE L'INSTALLATION ==="
echo "URL d'accès: http://192.168.1.33:8080/syscoa/"
echo "Utilisateur: admin"
echo "Mot de passe: admin123"
echo "Base de données: sysco_ohada"
echo "MySQL user: root"
echo "MySQL password: 123"
echo ""
echo "Pour tester:"
echo "1. Ouvrez http://192.168.1.33:8080/syscoa/ ou http://localhost:8080/syscoa/"
echo "2. Connectez-vous avec admin/admin123"
echo "3. Si problème: sudo tail -f /var/log/apache2/error.log"
