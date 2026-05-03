<?php
// public/fix_auth.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. On nettoie l'existant
    $db->exec("DELETE FROM USERS WHERE username = 'admin'");

    // 2. On crée le nouveau hash PHP pour le mot de passe "admin"
    $new_password = "admin";
    $hash = password_hash($new_password, PASSWORD_BCRYPT);

    // 3. Insertion propre
    $stmt = $db->prepare("INSERT INTO USERS (username, password_hash, role, is_active) VALUES (?, ?, 'admin', 1)");
    $stmt->execute(['admin', $hash]);

    echo "✅ Utilisateur 'admin' réinitialisé avec succès !<br>";
    echo "Mot de passe configuré sur : <b>admin</b><br>";
    echo "<a href='login.php'>Retourner à la page de connexion</a>";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
