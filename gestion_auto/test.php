<?php
echo "<h1>Test Simple</h1>";
echo "<p>PHP fonctionne</p>";

// Test de base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion_auto", "root", "123");
    echo "<p style='color: green;'>✓ Connexion DB réussie</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erreur DB: " . $e->getMessage() . "</p>";
}

// Test permissions uploads
$upload_dir = '/var/www/gestion_auto/uploads';
if (is_writable($upload_dir)) {
    echo "<p style='color: green;'>✓ Uploads accessible en écriture</p>";
} else {
    echo "<p style='color: red;'>✗ Uploads non accessible</p>";
}
?>
