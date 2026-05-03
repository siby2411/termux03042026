<?php
// Utilisation : create_user.php?user=momo&pass=123456
include 'db_connect.php';

if (isset($_GET['user']) && isset($_GET['pass'])) {
    $user = $_GET['user'];
    $pass = password_hash($_GET['pass'], PASSWORD_BCRYPT); // Hachage sécurisé
    
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $pass);
    
    if ($stmt->execute()) {
        echo "Utilisateur '$user' créé avec succès !";
    } else {
        echo "Erreur : " . $conn->error;
    }
} else {
    echo "Paramètres manquants dans l'URL (user et pass).";
}
?>
