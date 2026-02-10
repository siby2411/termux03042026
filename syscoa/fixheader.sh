#!/bin/bash
# fix_header_path.sh

echo "=== CORRECTION DU CHEMIN DANS HEADER.PHP ==="

echo "1. Vérification de la structure des dossiers..."
echo "   - /var/www/syscoa/config.php existe? $(if [ -f "/var/www/syscoa/config.php" ]; then echo "✅"; else echo "❌"; fi)"
echo "   - /var/www/syscoa/includes/header.php existe? $(if [ -f "/var/www/syscoa/includes/header.php" ]; then echo "✅"; else echo "❌"; fi)"

echo ""
echo "2. Correction du chemin dans header.php..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    # Sauvegarder
    sudo cp /var/www/syscoa/includes/header.php /var/www/syscoa/includes/header.php.backup
    
    # Remplacer la ligne 3
    sudo sed -i '3s|require_once ../config.php;|require_once __DIR__ . "/../config.php";|' /var/www/syscoa/includes/header.php
    
    echo "   ✅ Chemin corrigé"
    echo "   Nouvelle ligne 3:"
    sudo head -5 /var/www/syscoa/includes/header.php
else
    echo "   ❌ header.php non trouvé"
fi

echo ""
echo "3. Test de l'inclusion..."
# Créer un test
sudo tee /var/www/syscoa/test_header.php << 'EOF'
<?php
echo "Test d'inclusion de header.php...<br>";
$start_time = microtime(true);

try {
    include 'includes/header.php';
    echo "✅ header.php inclus avec succès<br>";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

$end_time = microtime(true);
echo "Temps d'exécution: " . round(($end_time - $start_time) * 1000, 2) . " ms";
?>
EOF

echo ""
echo "4. Test de syntaxe PHP..."
echo "   header.php: " && php -l /var/www/syscoa/includes/header.php 2>&1 | grep -o "No syntax errors" || echo "Erreur"

echo ""
echo "5. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION TERMINÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Accédez à : http://192.168.1.33:8080/syscoa/test_header.php"
echo "2. Puis essayez : http://192.168.1.33:8080/syscoa/login.php"
echo "3. Connectez-vous avec : admin / admin123"
echo ""
echo "📋 EN CAS D'ERREUR :"
echo "   sudo tail -f /var/log/apache2/error.log"
