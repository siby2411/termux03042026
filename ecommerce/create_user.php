<?php
require_once __DIR__ . "/src/includes/config.php";

$username = "momo";
$email = "momo@example.com";
$password = "123";
$role = "admin";

// Hash du mot de passe
$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users (username, email, password, role)
            VALUES (:username, :email, :password, :role)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hashed,
        ':role'     => $role
    ]);

    echo "✔ Utilisateur momo créé avec succès\n";

} catch (Exception $e) {
    echo "❌ ERREUR SQL : " . $e->getMessage() . "\n";
}

