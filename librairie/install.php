<?php
require_once 'includes/config.php';

// Vérifier si l'installation est déjà faite
$stmt = $pdo->query("SHOW TABLES LIKE 'utilisateurs'");
if ($stmt->rowCount() > 0) {
    die("L'application est déjà installée. Supprimez la base de données pour réinstaller.");
}

// Créer les tables
$sql = file_get_contents('includes/database_structure.sql');
$pdo->exec($sql);

// Créer l'utilisateur admin avec mot de passe hashé
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$nom = 'Administrateur';
$prenom = 'System';
$role = 'admin';

$stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, nom, prenom, role) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $password, $nom, $prenom, $role]);

echo "<h1>Installation terminée avec succès!</h1>";
echo "<p>Identifiants par défaut:</p>";
echo "<ul>";
echo "<li>Nom d'utilisateur: <strong>admin</strong></li>";
echo "<li>Mot de passe: <strong>admin123</strong></li>";
echo "</ul>";
echo "<a href='login.php' class='btn btn-primary'>Se connecter</a>";
?>
