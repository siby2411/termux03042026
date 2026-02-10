#!/bin/bash
# fix_index_500.sh

echo "=== CORRECTION DE L'ERREUR 500 DANS INDEX.PHP ==="

echo "1. Création de config_complet.php..."
if [ ! -f "/var/www/syscoa/config_complet.php" ]; then
    sudo cp /var/www/syscoa/config.php /var/www/syscoa/config_complet.php
    echo "   ✅ config_complet.php créé"
else
    echo "   ⏭️  config_complet.php existe déjà"
fi

echo ""
echo "2. Correction de la double session_start() dans config.php..."
if grep -q "session_start();" /var/www/syscoa/config.php; then
    sudo sed -i '3s/session_start();/if (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}/' /var/www/syscoa/config.php
    echo "   ✅ session_start() corrigé"
else
    echo "   ⏭️  session_start() déjà corrigé"
fi

echo ""
echo "3. Vérification de header.php..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    if grep -q "config_complet.php" /var/www/syscoa/includes/header.php; then
        echo "   ✅ header.php inclut config_complet.php (fichier maintenant disponible)"
    else
        echo "   ⚠️  header.php n'inclut pas config_complet.php"
    fi
fi

echo ""
echo "4. Vérification de index.php..."
if [ -f "/var/www/syscoa/index.php" ]; then
    echo "   Structure de index.php:"
    sudo head -15 /var/www/syscoa/index.php
fi

echo ""
echo "5. Test de la syntaxe PHP..."
echo "   - config.php: " && php -l /var/www/syscoa/config.php 2>/dev/null | grep -o "No syntax errors" || echo "Erreur"
echo "   - index.php: " && php -l /var/www/syscoa/index.php 2>/dev/null | grep -o "No syntax errors" || echo "Erreur"
echo "   - login.php: " && php -l /var/www/syscoa/login.php 2>/dev/null | grep -o "No syntax errors" || echo "Erreur"

echo ""
echo "6. Test de la connexion après login..."
# Créer un test de redirection
sudo tee /var/www/syscoa/test_redirect.php << 'EOF'
<?php
session_start();
$_SESSION['logged_in'] = true;
$_SESSION['username'] = 'admin';
$_SESSION['user_role'] = 'admin';
header('Location: index.php');
exit();
?>
EOF

echo "7. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION APPLIQUÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Allez sur : http://192.168.1.33:8080/syscoa/login.php"
echo "2. Connectez-vous avec : admin / admin123"
echo "3. Si toujours erreur 500, testez : http://192.168.1.33:8080/syscoa/test_redirect.php"
echo "4. Consultez les logs : sudo tail -f /var/log/apache2/error.log"
echo ""
echo "🔧 POUR DÉBOGUER :"
echo "   Test direct : curl -v http://localhost:8080/syscoa/index.php"
