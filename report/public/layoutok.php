<?php
// layout.php
if (!isset($page_title)) { $page_title = "Tableau de bord"; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= $page_title ?> - SynthesePro</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ----- BASE LAYOUT ----- */
body {
    margin: 0;
    padding: 0;
    background:#f5f7fb;
    overflow-x: hidden; /* empêche débordement horizontal */
}

/* ----- SIDEBAR ----- */
.sidebar {
    width: 270px;
    background: #0d1b2a;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    padding-top: 20px;
}

/* liens sidebar */
.sidebar a {
    color: #cfe0f5;
    padding:12px;
    display:block;
    font-size: 15px;
    text-decoration:none;
}
.sidebar a:hover {
    background:#1b263b;
    color:white;
    border-left:4px solid #4da3ff;
}

/* ----- MAIN CONTENT ----- */
.main-content {
    margin-left: 270px;
    padding: 20px;
}

/* barre titre */
.topbar {
    background:white;
    padding:15px;
    margin-bottom:20px;
    border-radius:8px;
    box-shadow:0 2px 4px rgba(0,0,0,0.06);
}

/* footer supprimé */
</style>

</head>

<body>


<?php include 'topbar.php'; ?>


<div class="sidebar">
    <h4 class="text-center mb-4">📘 SynthesePro</h4>

    <!-- Dashboard -->
    <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <!-- Comptabilité -->
    <a href="ecriture.php"><i class="bi bi-pencil-square"></i> Saisie d'écriture</a>
    <a href="list_ecriture.php"><i class="bi bi-bar-chart"></i> List Ecriture</a>
    <a href="comptes.php"><i class="bi bi-journal-text"></i> Plan Comptable</a>
    <a href="ajout_compte.php"><i class="bi bi-plus-circle"></i> Ajouter un compte</a>

    <!-- Analyse -->
    <a href="bilan.php"><i class="bi bi-columns-gap"></i> Bilan</a>
    <a href="list_bilan.php"><i class="bi bi-columns-gap"></i> List Bilan</a>
    <a href="resultat.php"><i class="bi bi-graph-up"></i> Résultat</a>
    <a href="list_resultat.php"><i class="bi bi-graph-up"></i> List Résultat</a>
    <a href="balance.php"><i class="bi bi-table"></i> Balance</a>

    <!-- Stocks / Immobilisations -->
    <a href="stock.php"><i class="bi bi-boxes"></i> Stock</a>
    <a href="list_stock.php"><i class="bi bi-bar-chart"></i> List Stock</a>
    <a href="immobilisations.php"><i class="bi bi-building"></i> Immobilisations</a>
    <a href="amortissements.php"><i class="bi bi-calculator"></i> Amortissements</a>

    <!-- Trésorerie -->
    <a href="flux_tresorerie.php"><i class="bi bi-cash-coin"></i> Flux de trésorerie</a>

    <!-- Grand Livre -->
 <a href="grand_livre.php"><i class="bi bi-journal"></i> Grand Livre</a>
 <a href="sig.php"><i class="bi bi-journal"></i> SIG</a>

    <!-- Dashboard graphique -->
    <a href="dashboard_graphic.php"><i class="bi bi-bar-chart"></i> Dashboard Graphique</a>
</div>







<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="topbar">
        <h4><?= $page_title ?></h4>
    </div>


