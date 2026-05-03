<?php
include 'db_connect_ecole.php';
$conn = db_connect_ecole();

// 1. Nettoyage et Création de la table avec la structure EXACTE du login.php
$conn->query("DROP TABLE IF EXISTS utilisateurs_ecole");
$sql = "CREATE TABLE utilisateurs_ecole (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'professeur', 'comptable', 'etudiant') DEFAULT 'admin',
    id_entite_associe INT DEFAULT NULL,
    entite_type VARCHAR(50) DEFAULT NULL,
    nom_complet VARCHAR(100)
)";
$conn->query($sql);

// 2. Préparation des comptes de test
$users = [
    ['admin', 'admin123', 'admin', 'MOHAMED SIBY', 'ADMIN'],
    ['prof.diop', 'prof123', 'professeur', 'M. DIOP', 'PROFESSEUR'],
    ['ETU-2025-0001', 'etu123', 'etudiant', 'Etudiant Test', 'ETUDIANT']
];

foreach ($users as $u) {
    $hashed = password_hash($u[1], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO utilisateurs_ecole (username, password, role, nom_complet, entite_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $u[0], $hashed, $u[2], $u[3], $u[4]);
    $stmt->execute();
}

echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
echo "<h1 style='color:#D4AF37;'>✨ Accès OMEGA ÉCOLE Réinitialisés !</h1>";
echo "<p>Les comptes <b>admin</b>, <b>prof.diop</b> et <b>ETU-2025-0001</b> sont prêts.</p>";
echo "<a href='login.php' style='padding:10px 20px; background:#1a2a6c; color:white; text-decoration:none; border-radius:5px;'>Aller à la Connexion</a>";
echo "</div>";
?>
