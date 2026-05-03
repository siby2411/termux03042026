<?php require_once 'header.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-users"></i> Clients</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="clients/liste.php">Liste</a></li><li><a class="dropdown-item" href="clients/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-truck"></i> Fournisseurs</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="fournisseurs/liste.php">Liste</a></li><li><a class="dropdown-item" href="fournisseurs/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-boxes"></i> Produits</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="produits/liste.php">Liste</a></li><li><a class="dropdown-item" href="produits/ajouter.php">Ajouter</a></li><li><a class="dropdown-item" href="categories/liste.php">Catégories</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-shopping-cart"></i> Commandes</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="commandes/liste.php">Liste</a></li><li><a class="dropdown-item" href="commandes/ajouter.php">Nouvelle</a></li></ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="panier/index.php"><i class="fas fa-shopping-basket"></i> Panier</a></li>
                <li class="nav-item"><a class="nav-link" href="sessions/liste.php"><i class="fas fa-history"></i> Sessions</a></li>
            </ul>
            <span class="navbar-text me-3">
                <i class="fas fa-user-circle"></i>
                <?php if (isset($_SESSION['user_name'])): ?>
                    <?= escape($_SESSION['user_name']) ?> (<?= escape($_SESSION['user_type']) ?>)
                <?php else: ?>
                    Non connecté
                <?php endif; ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>
<style>
.navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.navbar .nav-link:hover { color: #ff6b6b !important; transform: translateY(-2px); transition: all 0.3s; }
.dropdown-menu { background: #2c3e50; border: none; }
.dropdown-item { color: #ecf0f1; transition: all 0.3s; }
.dropdown-item:hover { background: #ff6b6b; color: white; transform: translateX(5px); }
.btn-outline-light:hover { background: #ff6b6b; border-color: #ff6b6b; }
</style>
