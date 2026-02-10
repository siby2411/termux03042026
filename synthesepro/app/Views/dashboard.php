<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>✅ Dashboard SynthesePro</h1>
<p>Utilisateur connecté : <?=htmlspecialchars($_SESSION['user_role'])?></p>
<p><a href="index.php?action=logout">Déconnexion</a></p>
</body>
</html>

<?php
// Déconnexion
if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

