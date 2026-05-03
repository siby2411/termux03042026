<?php
$user = Auth::getUser();
$active_menu = $active_menu ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($page_title ?? 'PharmaSen') ?> — Omega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{ --og:#00713e; --od:#01291a; --gold:#f5a800; --sw:255px; }
        body{ font-family:'Inter', sans-serif; background:#f0f5f2; margin:0; }
        .omega-band{ height:54px; background:linear-gradient(135deg, var(--od), var(--og)); position:fixed; top:0; left:0; right:0; z-index:1100; display:flex; align-items:center; padding:0 20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        .omega-sidebar{ position:fixed; top:54px; left:0; bottom:0; width:var(--sw); background:var(--od); z-index:1000; padding-top:10px; display:flex; flex-direction:column; overflow-y:auto; }
        .sidebar-item{ display:flex; align-items:center; gap:12px; padding:12px 20px; color:rgba(255,255,255,0.7); text-decoration:none; font-size:0.85rem; border-left:4px solid transparent; transition: 0.2s; }
        .sidebar-item:hover, .sidebar-item.active{ background:rgba(255,255,255,0.1); color:white; border-left-color:var(--gold); }
        .sidebar-section{ padding:15px 20px 5px; font-size:0.6rem; color:rgba(255,255,255,0.3); text-transform:uppercase; letter-spacing:1px; font-weight:bold; }
        .main-wrap{ margin-left:var(--sw); margin-top:54px; padding:25px; min-height:90vh; }
        .logout-box{ margin-top: auto; padding-bottom: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="omega-band text-white">
        <b style="color:var(--gold)">Ω OMEGA PHARMA</b>
        <span class="ms-3 small opacity-75 d-none d-md-inline">Système de Gestion — Mr Mohamed Siby</span>
        <div class="ms-auto small">
            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?>
        </div>
    </div>

    <div class="omega-sidebar">
        <div class="sidebar-section">Tableau de bord</div>
        <a href="/modules/dashboard/index.php" class="sidebar-item <?= $active_menu=='dashboard'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
        
        <div class="sidebar-section">Ventes & Clients</div>
        <a href="/modules/caisse/pos.php" class="sidebar-item <?= $active_menu=='pos'?'active':'' ?>"><i class="bi bi-cart3"></i> Ventes (Caisse)</a>
        <a href="/modules/ventes/index.php" class="sidebar-item <?= $active_menu=='ventes'?'active':'' ?>"><i class="bi bi-receipt"></i> Historique Ventes</a>
        
        <div class="sidebar-section">Stock & Logistique</div>
        <a href="/modules/medicaments/index.php" class="sidebar-item <?= $active_menu=='medicaments'?'active':'' ?>"><i class="bi bi-capsule"></i> Médicaments</a>
        <a href="/modules/stock/index.php" class="sidebar-item <?= $active_menu=='stock'?'active':'' ?>"><i class="bi bi-boxes"></i> État des Stocks</a>
        <a href="/modules/fournisseurs/index.php" class="sidebar-item <?= $active_menu=='fournisseurs'?'active':'' ?>"><i class="bi bi-truck"></i> Fournisseurs</a>
        <a href="/modules/achats/index.php" class="sidebar-item <?= $active_menu=='achats'?'active':'' ?>"><i class="bi bi-cart-plus"></i> Achats / Entrées</a>
        
        <div class="sidebar-section">Analyses</div>
        <a href="/modules/rapports/index.php" class="sidebar-item <?= $active_menu=='rapports'?'active':'' ?>"><i class="bi bi-bar-chart-line"></i> Rapports</a>

        <div class="logout-box">
            <a href="/logout.php" class="sidebar-item text-danger fw-bold">
                <i class="bi bi-power"></i> DÉCONNEXION
            </a>
        </div>
    </div>

    <div class="main-wrap">
