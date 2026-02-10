<?php
if (!isset($page_title)) $page_title = "SYSCOHADA PRO";

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/report/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {font-family: "Segoe UI", sans-serif; background: #f5f7fb;}
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh; width: 260px;
            background-color: #1f2937; color: #fff; padding-top: 20px;
        }
        .sidebar h4 {color: #0ea5e9;}
        .sidebar a {color: #cbd5e1; display:block; padding:12px 20px; text-decoration:none;}
        .sidebar a:hover, .sidebar a.active {background:#0ea5e9; color:#fff;}
        .content {margin-left: 260px; padding: 25px;}
        .topbar {background: #fff; padding:12px 25px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); position:sticky; top:0; z-index:10;}
        footer {margin-left: 260px; padding:15px 25px; color:#666;}
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">📊 SYSCOHADA PRO</h4>
    <a href="/report/public/admin_dashboard.php" class="<?= ($page_title=='Dashboard')?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/report/public/ecriture.php" class="<?= ($page_title=='Saisie des écritures')?'active':'' ?>"><i class="bi bi-pencil-square"></i> Écritures</a>
    <a href="/report/public/bilan.php" class="<?= ($page_title=='Bilan')?'active':'' ?>"><i class="bi bi-building"></i> Bilan</a>
    <a href="/report/public/sig.php" class="<?= ($page_title=='SIG')?'active':'' ?>"><i class="bi bi-graph-up"></i> SIG</a>
    <a href="/report/public/flux.php" class="<?= ($page_title=='Flux')?'active':'' ?>"><i class="bi bi-cash-stack"></i> Flux de trésorerie</a>
    <a href="/report/public/stock.php" class="<?= ($page_title=='Stock')?'active':'' ?>"><i class="bi bi-box-seam"></i> Stock</a>
    <a href="/report/public/immobilisation.php" class="<?= ($page_title=='Immobilisation')?'active':'' ?>"><i class="bi bi-building"></i> Immobilisations</a>
    <a href="/report/public/comptes.php" class="<?= ($page_title=='Comptes')?'active':'' ?>"><i class="bi bi-journal-bookmark"></i> Comptes</a>
    <a href="/report/public/resultat.php" class="<?= ($page_title=='Résultat')?'active':'' ?>"><i class="bi bi-bar-chart-line"></i> Résultat</a>
    <a href="/report/public/balance.php" class="<?= ($page_title=='Balance')?'active':'' ?>"><i class="bi bi-journal-check"></i> Balance</a>
    <a href="/report/public/ajout_compte.php"><i class="bi bi-journal-plus"></i> Ajouter un compte</a>
    <a href="/report/public/logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
</div>

<!-- Main content -->
<div class="content">
    <div class="topbar">
        <h4><?= $page_title ?></h4>
    </div>

