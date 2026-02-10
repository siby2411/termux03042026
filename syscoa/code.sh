#!/bin/bash
# fix_php_code_visible.sh

echo "=== CORRECTION DU CODE PHP VISIBLE DANS LES PAGES ==="

echo "1. Sauvegarde des fichiers actuels..."
sudo cp /var/www/syscoa/config.php /var/www/syscoa/config.php.bak.visible
sudo cp /var/www/syscoa/login.php /var/www/syscoa/login.php.bak.visible

echo "2. Vérification des fichiers pour code hors balises PHP..."
echo "   - config.php:"
sudo grep -n "^[^<?php\|^//\|^/*\|^*\|^?>\|^\s*$]" /var/www/syscoa/config.php | head -10

echo "   - login.php:"
sudo grep -n "^[^<?php\|^//\|^/*\|^*\|^?>\|^\s*$]" /var/www/syscoa/login.php | head -10

echo ""
echo "3. Correction de config.php..."
# S'assurer qu'il n'y a qu'une seule balise ?> à la fin
# Compter les balises ?>
TAG_COUNT=$(sudo grep -c "?>" /var/www/syscoa/config.php)
if [ "$TAG_COUNT" -gt 1 ]; then
    echo "   ⚠  Plusieurs balises ?> détectées ($TAG_COUNT)"
    # Garder seulement la première balise ?>
    sudo sed -i '/?>/{$!N; /?>.*?>/s/?>//; }' /var/www/syscoa/config.php
fi

echo ""
echo "4. Vérification de la syntaxe PHP..."
php -l /var/www/syscoa/config.php
php -l /var/www/syscoa/login.php

echo ""
echo "5. Test avec un fichier simple..."
sudo tee /var/www/syscoa/test_clean.php << 'EOF'
<?php
require_once 'config.php';
echo "Test de config.php - Si ce texte s'affiche, c'est bon.";
?>
EOF

echo ""
echo "6. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION TERMINÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Test simple: http://192.168.1.33:8080/syscoa/test_clean.php"
echo "2. Page de login: http://192.168.1.33:8080/syscoa/login.php"
echo "   → Ne devrait plus afficher de code PHP"
echo ""
echo "📊 SI LE PROBLÈME PERSISTE :"
echo "   Vérifiez aussi index.php et autres fichiers:"
echo "   sudo grep -n '^[^<?php\|^//\|^/*\|^*\|^?>\|^\s*$]' /var/www/syscoa/*.php"
