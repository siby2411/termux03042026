<?php
include_once 'config/Database.php';
$db = (new Database())->getConnection();
$pass = password_hash('admin', PASSWORD_BCRYPT); // Génère le vrai hash
$db->prepare("REPLACE INTO USERS (id_user, username, password_hash, role, is_active) 
              VALUES (1, 'admin', ?, 'admin', 1)")->execute([$pass]);
echo "Utilisateur admin mis à jour avec le bon hash !";
?>
