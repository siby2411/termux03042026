#!/bin/bash
# fix_missing_constants_final.sh

echo "=== CORRECTION DES CONSTANTES MANQUANTES FINALE ==="

echo "1. Vérification de la ligne 180 dans header.php..."
sudo sed -n '175,185p' /var/www/syscoa/includes/header.php

echo ""
echo "2. Ajout des constantes manquantes dans config.php..."

# Vérifier et ajouter COMPANY_NAME
if ! sudo grep -q "COMPANY_NAME" /var/www/syscoa/config.php; then
    echo "   → Ajout de COMPANY_NAME"
    sudo tee -a /var/www/syscoa/config.php << 'EOF'

// Information entreprise
define('COMPANY_NAME', 'SYSCOHADA Entreprise');
define('COMPANY_ADDRESS', '123 Rue des Comptables, Ville');
define('COMPANY_PHONE', '+225 01 23 45 67 89');
define('COMPANY_EMAIL', 'contact@syscohada.local');
EOF
else
    echo "   ✓ COMPANY_NAME déjà définie"
fi

# Vérifier si d'autres constantes utilisées dans header.php manquent
echo ""
echo "3. Vérification des autres constantes potentielles manquantes..."

# Liste des constantes couramment utilisées
CONSTANTS=("COMPANY_NAME" "COMPANY_ADDRESS" "COMPANY_PHONE" "COMPANY_EMAIL" 
           "SYSCOHADA_VERSION" "SITE_NAME" "SITE_URL" "DEFAULT_MODULE" 
           "DEFAULT_SUBMODULE" "CURRENCY" "DATE_FORMAT")

for const in "${CONSTANTS[@]}"; do
    if ! sudo grep -q "define.*$const" /var/www/syscoa/config.php; then
        echo "   ⚠  $const n'est pas définie dans config.php"
    fi
done

echo ""
echo "4. Vérification de la syntaxe de config.php..."
php -l /var/www/syscoa/config.php

echo ""
echo "5. Tester l'inclusion de config.php..."
sudo tee /var/www/syscoa/test_constants2.php << 'EOF'
<?php
require_once 'config.php';

echo "<h2>Test des constantes - Partie 2</h2>";
echo "COMPANY_NAME: " . (defined('COMPANY_NAME') ? COMPANY_NAME : 'NON DÉFINIE') . "<br>";
echo "SITE_NAME: " . (defined('SITE_NAME') ? SITE_NAME : 'NON DÉFINIE') . "<br>";
echo "SYSCOHADA_VERSION: " . (defined('SYSCOHADA_VERSION') ? SYSCOHADA_VERSION : 'NON DÉFINIE') . "<br>";

// Tester l'inclusion de header
echo "<h3>Test d'inclusion de header.php</h3>";
ob_start();
try {
    include 'includes/header.php';
    $header_content = ob_get_clean();
    echo "✅ header.php inclus avec succès<br>";
    echo "Longueur du contenu: " . strlen($header_content) . " caractères";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
EOF

echo ""
echo "6. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION APPLIQUÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Test des constantes : http://192.168.1.33:8080/syscoa/test_constants2.php"
echo "2. Page de connexion : http://192.168.1.33:8080/syscoa/login.php"
echo "3. Connectez-vous avec admin/admin123"
echo ""
echo "📊 SI TOUJOURS ERREUR :"
echo "   Vérifiez toutes les constantes utilisées dans header.php :"
echo "   sudo grep -o \"[A-Z_][A-Z0-9_]*\" /var/www/syscoa/includes/header.php | sort -u"
