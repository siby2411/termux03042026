

<?php
session_start();
require_once __DIR__ . "/../src/includes/config.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Récupérer le nom de l'utilisateur
$username = $_SESSION['user'];

require_once __DIR__ . "/../src/includes/header.php";
?>

<div class="row">
    <div class="col-md-12 text-center mb-4">
        <h1>Bienvenue <?= htmlspecialchars($username) ?> !</h1>
        <p>Gestion complète de l'e-commerce PME</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-box-seam"></i> Produits</h5>
                <p class="card-text">Ajouter, modifier ou supprimer des produits</p>
                <a href="produits/list.php" class="btn btn-light btn-sm">Gérer Produits</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people"></i> Clients</h5>
                <p class="card-text">Suivi et gestion des clients</p>
                <a href="#" class="btn btn-light btn-sm">Gérer Clients</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-truck"></i> Fournisseurs</h5>
                <p class="card-text">Gestion des fournisseurs et stocks</p>
                <a href="#" class="btn btn-light btn-sm">Gérer Fournisseurs</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../src/includes/footer.php"; ?>

