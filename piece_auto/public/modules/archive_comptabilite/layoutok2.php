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
    overflow-x: hidden;
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
    overflow-y: auto;
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
    overflow-x: hidden;
}

/* ----- TOPBAR HORIZONTAL ----- */
.topbar-horizontal {
    position: sticky;
    top: 0;
    z-index: 1050;
    background: #ffffff;
    padding: 10px 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* Dashboard cards */
.card-dashboard {
    text-align: center;
    padding: 20px;
    transition: transform 0.2s;
}
.card-dashboard:hover {
    transform: translateY(-3px);
}
</style>
</head>

<body>

<!-- SIDEBAR -->
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
    <a href="resultat.php"><i class="bi bi-graph-up"></i> Résultat</a>
    <a href="balance.php"><i class="bi bi-table"></i> Balance</a>
    <a href="sig.php"><i class="bi bi-bar-chart-line"></i> SIG</a>

    <!-- Stocks / Immobilisations -->
    <a href="stock.php"><i class="bi bi-boxes"></i> Stock</a>
    <a href="list_stock.php"><i class="bi bi-bar-chart"></i> List Stock</a>
    <a href="immobilisations.php"><i class="bi bi-building"></i> Immobilisations</a>
    <a href="amortissements.php"><i class="bi bi-calculator"></i> Amortissements</a>

    <!-- Trésorerie -->
    <a href="flux_tresorerie.php"><i class="bi bi-cash-coin"></i> Flux de trésorerie</a>

    <!-- Grand Livre -->
    <a href="grand_livre.php"><i class="bi bi-journal"></i> Grand Livre</a>

    <!-- Dashboard graphique -->
    <a href="dashboard_graphic.php"><i class="bi bi-bar-chart"></i> Dashboard Graphique</a>

    <!-- FUTURS MODULES -->
    <a href="rapprochement.php"><i class="bi bi-bank"></i> Rapprochement bancaire</a>
    <a href="variation_capitaux.php"><i class="bi bi-diagram-3"></i> Variation Capitaux</a>
    <a href="controle_budget.php"><i class="bi bi-check2-square"></i> Contrôle Budgétaire</a>
    <a href="ratios_financiers.php"><i class="bi bi-gear"></i> Ratios Financiers</a>

<a href="controle_budget.php"><i class="bi bi-check2-square"></i> Contrôle Budgétaire</a>
    <a href="a_nouveaux.php"><i class="bi bi-gear"></i> Gestion des A Nouveau</a>




</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- TOPBAR HORIZONTAL -->
    <div class="topbar-horizontal">
        <div class="fw-bold fs-5 text-primary">📘 SynthesePro</div>
        <div>
            <a href="profile.php" class="me-3 text-decoration-none"><i class="bi bi-person-circle"></i> Profil</a>
            <a href="logout.php" class="text-decoration-none text-danger"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
        </div>
    </div>

    <!-- PAGE TITLE -->
    <div class="topbar">
        <h4><?= $page_title ?></h4>
    </div>

    <!-- DASHBOARD CARDS (ÉTATS FINANCIERS) -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card card-dashboard shadow-sm">
                <h6>Balance</h6>
                <i class="bi bi-table fs-1 text-primary"></i>
                <a href="balance.php" class="btn btn-sm btn-primary mt-2">Voir</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-dashboard shadow-sm">
                <h6>Bilan</h6>
                <i class="bi bi-columns-gap fs-1 text-success"></i>
                <a href="bilan.php" class="btn btn-sm btn-success mt-2">Voir</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-dashboard shadow-sm">
                <h6>Résultat</h6>
                <i class="bi bi-graph-up fs-1 text-warning"></i>
                <a href="resultat.php" class="btn btn-sm btn-warning mt-2">Voir</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-dashboard shadow-sm">
                <h6>Flux de trésorerie</h6>
                <i class="bi bi-cash-coin fs-1 text-danger"></i>
                <a href="flux_tresorerie.php" class="btn btn-sm btn-danger mt-2">Voir</a>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-dashboard shadow-sm">
                <h6>SIG</h6>
                <i class="bi bi-bar-chart-line fs-1 text-info"></i>
                <a href="sig.php" class="btn btn-sm btn-info mt-2">Voir / Export Excel</a>
            </div>
        </div>
    </div>

<!-- Le contenu spécifique à chaque page commencera ici -->

