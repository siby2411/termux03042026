<?php
require_once 'db_connect_ecole.php';

$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$admin_email = "admin@ecole.local";
$role = "admin";

// Vérifier si l'admin existe déjà
$sql = "SELECT * FROM utilisateurs_ecole WHERE username = '$admin_username'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql_insert = "INSERT INTO utilisateurs_ecole (username, email, password, role) VALUES ('$admin_username', '$admin_email', '$admin_password', '$role')";
    if ($conn->query($sql_insert) === TRUE) {
        echo "Admin créé avec succès !<br>";
        echo "<a href='login.php'>Aller à la page de connexion</a>";
    } else {
        echo "Erreur lors de la création : " . $conn->error;
    }
} else {
    echo "L'admin existe déjà. <a href='login.php'>Aller à la page de connexion</a>";
}
?>

