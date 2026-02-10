<?php
if (!isset($page_title)) { $page_title = "SynthesePro"; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?= htmlspecialchars($page_title) ?></title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body { background: #f5f6fa; }

    /* SIDEBAR DESKTOP */
    #sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #202940;
        padding-top: 60px;
        z-index: 1020;
        overflow-y: auto;
    }
    #sidebar a {
        color: #fff;
        padding: 12px 20px;
        display: block;
        text-decoration: none;
        font-size: 15px;
    }
    #sidebar a:hover { background: #1b2234; }

    /* CONTENU */
    @media(min-width: 992px) { #content { margin-left: 250px; } }

    .offcanvas a { text-decoration: none; font-size: 16px; }
    .navbar-dark .navbar-brand { color: #fff; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- BOUTON MOBILE -->
        <button class="btn btn-outline-light d-lg-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#menuMobile"
                aria-controls="menuMobile">
            <i class="bi bi-list"></i> Menu
        </button>
        <span class="navbar-brand">SynthesePro</span>
    </div>
</nav>

<!-- SIDEBAR DESKTOP -->
<div id="sidebar" class="d-none d-lg-block">
    <a href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

    <!-- Comptabilité -->
    <a href="ecriture.php"><i class="bi bi-pencil-square me-2"></i> Saisie d'écriture</a>
    <a href="list_ecriture.php"><i class="bi bi-bar-chart me-2"></i> List Ecriture</a>
    <a href="comptes.php"><i class="bi bi-journal-text me-2"></i> Plan Comptable</a>
    <a href="ajout_compte.php"><i class="bi bi-plus-circle me-2"></i> Ajouter un compte</a>

    <!-- Analyse -->
    <a href="bilan.php"><i class="bi bi-columns-gap me-2"></i> Bilan</a>
    <a href="list_bilan.php"><i class="bi bi-columns-gap me-2"></i> List Bilan</a>
    <a href="resultat.php"><i class="bi bi-graph-up me-2"></i> Résultat</a>

    <a href="balance.php"><i class="bi bi-table me-2"></i> Balance</a>

    <!-- Stocks / Immobilisations -->
    <a href="stock.php"><i class="bi bi-boxes me-2"></i> Stock</a>
    <a href="list_stock.php"><i class="bi bi-bar-chart me-2"></i> List Stock</a>
    <a href="immobilisations.php"><i class="bi bi-building me-2"></i> Immobilisations</a>
    <a href="amortissements.php"><i class="bi bi-calculator me-2"></i> Amortissements</a>

    <!-- Trésorerie -->
    <a href="flux_tresorerie.php"><i class="bi bi-cash-coin me-2"></i> Flux de trésorerie</a>

    <!-- Grand Livre -->
    <a href="grand_livre.php"><i class="bi bi-journal me-2"></i> Grand Livre</a>

    <!-- Dashboard graphique -->
    <a href="dashboard_graphic.php"><i class="bi bi-bar-chart me-2"></i> Dashboard Graphique</a>

    <!-- Modules supplémentaires -->
    <a href="ecrit.php"><i class="bi bi-pencil-square me-2"></i> list_anouveau.php</a>
    <a href="list_anouveau.php"><i class="bi bi-pencil-square me-2"></i> list_anouveau.php</a>

    <a href="examplesops.html"><i class="bi bi-journal-text me-2"></i> examples ops</a>
    <a href="reg_passif.php"><i class="bi bi-plus-circle me-2"></i> Regularisation passif</a>
</div>

<!-- OFFCANVAS MOBILE -->
<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="menuMobile" aria-labelledby="menuMobileLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="menuMobileLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body">
        <a href="admin_dashboard.php" class="text-white d-block mb-2"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

        <!-- Comptabilité -->
        <a href="ecriture.php" class="text-white d-block mb-2"><i class="bi bi-pencil-square me-2"></i> Saisie d'écriture</a>
        <a href="list_ecriture.php" class="text-white d-block mb-2"><i class="bi bi-bar-chart me-2"></i> List Ecriture</a>
        <a href="comptes.php" class="text-white d-block mb-2"><i class="bi bi-journal-text me-2"></i> Plan Comptable</a>
        <a href="ajout_compte.php" class="text-white d-block mb-2"><i class="bi bi-plus-circle me-2"></i> Ajouter un compte</a>

        <!-- Analyse -->
        <a href="bilan.php" class="text-white d-block mb-2"><i class="bi bi-columns-gap me-2"></i> Bilan</a>
        <a href="list_bilan.php" class="text-white d-block mb-2"><i class="bi bi-columns-gap me-2"></i> List Bilan</a>
        <a href="resultat.php" class="text-white d-block mb-2"><i class="bi bi-graph-up me-2"></i> Résultat</a>
        <a href="list_resultat.php" class="text-white d-block mb-2"><i class="bi bi-graph-up me-2"></i> List Résultat</a>
        <a href="balance.php" class="text-white d-block mb-2"><i class="bi bi-table me-2"></i> Balance</a>

        <!-- Stocks / Immobilisations -->
        <a href="stock.php" class="text-white d-block mb-2"><i class="bi bi-boxes me-2"></i> Stock</a>
        <a href="list_stock.php" class="text-white d-block mb-2"><i class="bi bi-bar-chart me-2"></i> List Stock</a>
        <a href="immobilisations.php" class="text-white d-block mb-2"><i class="bi bi-building me-2"></i> Immobilisations</a>
        <a href="amortissements.php" class="text-white d-block mb-2"><i class="bi bi-calculator me-2"></i> Amortissements</a>

        <!-- Trésorerie -->
        <a href="flux_tresorerie.php" class="text-white d-block mb-2"><i class="bi bi-cash-coin me-2"></i> Flux de trésorerie</a>

        <!-- Grand Livre -->
        <a href="grand_livre.php" class="text-white d-block mb-2"><i class="bi bi-journal me-2"></i> Grand Livre</a>

        <!-- Dashboard graphique -->
        <a href="dashboard_graphic.php" class="text-white d-block mb-2"><i class="bi bi-bar-chart me-2"></i> Dashboard Graphique</a>

        <!-- Modules supplémentaires -->
        <a href="ecrit.php" class="text-white d-block mb-2"><i class="bi bi-pencil-square me-2"></i> list_anouveau.php</a>
        <a href="list_anouveau.php" class="text-white d-block mb-2"><i class="bi bi-pencil-square me-2"></i> list_anouveau.php</a>
        <a href="log.php" class="text-white d-block mb-2"><i class="bi bi-bar-chart me-2"></i> log.phpin test</a>
        <a href="examplesops.html" class="text-white d-block mb-2"><i class="bi bi-journal-text me-2"></i> examples ops</a>
        <a href="reg_passif.php" class="text-white d-block mb-2"><i class="bi bi-plus-circle me-2"></i> Regularisation passif</a>
    </div>
</div>

<!-- CONTENU -->
<div id="content" class="container-fluid mt-5 pt-4">

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

