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
