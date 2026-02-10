<?php
// /var/www/piece_auto/hash_generator.php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mot de passe: " . $password . "\n";
echo "Hachage généré: " . $hash . "\n";
?>
