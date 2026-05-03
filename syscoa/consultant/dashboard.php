<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Administrateur</title>
</head>
<body>
    <h1>Bienvenue, administrateur</h1>
    <p>Vous êtes connecté en tant que <?php echo $_SESSION['username']; ?></p>
    <a href="../logout.php">Déconnexion</a>
</body>
</html>
