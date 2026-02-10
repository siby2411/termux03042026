#!/bin/bash
# check_compatibility.sh

echo "=== VÉRIFICATION DE COMPATIBILITÉ SYSCOHADA ==="

echo "1. Vérification des fichiers PHP essentiels..."
echo "   - config.php: $(if [ -f "/var/www/syscoa/config.php" ]; then echo "✅"; else echo "❌"; fi)"
echo "   - login.php: $(if [ -f "/var/www/syscoa/login.php" ]; then echo "✅"; else echo "❌"; fi)"
echo "   - index.php: $(if [ -f "/var/www/syscoa/index.php" ]; then echo "✅"; else echo "❌"; fi)"
echo "   - includes/header.php: $(if [ -f "/var/www/syscoa/includes/header.php" ]; then echo "✅"; else echo "❌"; fi)"

echo ""
echo "2. Vérification des fonctions dans config.php..."
sudo grep -n "function " /var/www/syscoa/config.php

echo ""
echo "3. Vérification de la session dans config.php..."
sudo grep -n "session" /var/www/syscoa/config.php

echo ""
echo "4. Vérification de la structure de index.php..."
echo "=== PREMIÈRES 20 LIGNES DE INDEX.PHP ==="
sudo head -20 /var/www/syscoa/index.php
echo ""
echo "=== DERNIÈRES 10 LIGNES DE INDEX.PHP ==="
sudo tail -10 /var/www/syscoa/index.php

echo ""
echo "5. Test des inclusions PHP..."
# Créer un test d'inclusion minimal
sudo tee /var/www/syscoa/test_minimal.php << 'EOF'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: Inclusion de config.php<br>";
include 'config.php';
echo "✅ config.php chargé<br>";

echo "Test 2: Vérification des fonctions<br>";
if (function_exists('check_login')) {
    echo "✅ Fonction check_login() existe<br>";
} else {
    echo "❌ Fonction check_login() n'existe pas<br>";
}

echo "Test 3: Session<br>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'test';
echo "✅ Session fonctionnelle<br>";

echo "Test 4: Connexion BD<br>";
try {
    $pdo = get_db_connection();
    echo "✅ Connexion BD réussie<br>";
} catch (Exception $e) {
    echo "❌ Erreur BD: " . $e->getMessage() . "<br>";
}

echo "<br>=== TEST RÉUSSI ===";
?>
EOF

echo ""
echo "6. Vérification des permissions..."
ls -la /var/www/syscoa/*.php | head -5
ls -la /var/www/syscoa/includes/*.php 2>/dev/null | head -5

echo ""
echo "7. Test avec curl..."
echo "   Test minimal:"
curl -s "http://localhost:8080/syscoa/test_minimal.php" | head -5

echo ""
echo "=== RÉSULTATS DE LA VÉRIFICATION ==="
echo "Accédez à: http://192.168.1.33:8080/syscoa/test_minimal.php"
echo "Si tout est vert, alors index.php devrait fonctionner."
echo "Si problème, voyez les erreurs sur la page de test."
