<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Omega Market</a>
        <div class="navbar-nav">
            <a class="nav-link" href="crud_produits.php">Produits</a>
            <a class="nav-link" href="crud_clients.php">Clients</a>
            <a class="nav-link" href="crud_appro.php">Appro</a>
            <a class="nav-link" href="facturation.php">Ventes</a>
            <?php if(isset($_SESSION['id_vendeur'])): ?>
                <a class="nav-link text-warning" href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a class="nav-link text-white fw-bold" href="login.php">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">
