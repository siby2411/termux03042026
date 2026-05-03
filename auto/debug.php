<?php
include 'db_connect.php';
$res = $conn->query("SELECT * FROM users WHERE username='admin'");
$user = $res->fetch_assoc();
echo "Utilisateur trouvé : " . $user['username'] . "<br>";
echo "Mot de passe en base : [" . $user['password'] . "] (Longueur: " . strlen($user['password']) . ")";
?>
