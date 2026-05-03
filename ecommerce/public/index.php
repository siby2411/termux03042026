<?php
// ==========================
// File: public/index.php
// ==========================
?>
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-center">Bienvenue dans l'e-commerce</h1>
    <div class="text-center mt-4">
        <a href="list.php" class="btn btn-primary">Gérer les Produits</a>
        <a href="logout.php" class="btn btn-danger">Déconnexion</a>
    </div>
</div>
</body>
</html>
