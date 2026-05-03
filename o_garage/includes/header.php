<?php
require_once __DIR__ . '/classes/Database.php';
$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA TECH ERP - Engineering Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --om-dark: #0a0a0a; --om-orange: #ff6d00; --om-blue: #0d47a1; }
        body { background: #f0f2f5; font-size: 0.85rem; padding-top: 70px; }
        .navbar-omega { background: var(--om-dark); border-bottom: 3px solid var(--om-orange); }
        .nav-link { font-weight: 600; text-transform: uppercase; color: #ddd !important; }
        .nav-link:hover { color: var(--om-orange) !important; }
        .dropdown-menu { border-top: 3px solid var(--om-orange); border-radius: 0; font-size: 0.85rem; box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .dropdown-item i { width: 25px; color: var(--om-blue); }
        .btn-omega { background: var(--om-orange); color: white; font-weight: bold; border: none; }
        .btn-omega:hover { background: #e65100; color: white; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-xl navbar-dark navbar-omega fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/index.php">
            <i class="fas fa-engine"></i> OMEGA <span style="color:var(--om-orange)">TECH</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#erpNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="erpNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">🚗 Parc</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/scripts/clients/liste_clients.php"><i class="fas fa-users"></i> Clients</a></li>
                        <li><a class="dropdown-item" href="/scripts/vehicules/fiche_entree.php"><i class="fas fa-file-import"></i> Entrée Atelier</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">🛠️ Atelier</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/scripts/diagnostics/recherche_panne.php"><i class="fas fa-search"></i> Cartographie Pannes</a></li>
                        <li><a class="dropdown-item" href="/scripts/interventions/dashboard_ingenieur.php"><i class="fas fa-microchip"></i> Diagnostics</a></li>
                        <li><a class="dropdown-item" href="/scripts/suivi/etat_avancement.php"><i class="fas fa-spinner"></i> Suivi en temps réel</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-warning" href="#" data-bs-toggle="dropdown">💰 Finance</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/scripts/factures/reparation.php"><i class="fas fa-file-invoice"></i> Facturer Réparation</a></li>
                        <li><a class="dropdown-item" href="/scripts/factures/liste_reparations.php"><i class="fas fa-history"></i> Journal des ventes</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">📦 Logistique</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/scripts/pieces/liste_pieces.php"><i class="fas fa-boxes"></i> Stock Pièces</a></li>
                        <li><a class="dropdown-item" href="/scripts/fournisseurs/liste_fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">👥 RH</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/scripts/mecaniciens/liste_mecaniciens.php"><i class="fas fa-user-cog"></i> Équipe</a></li>
                        <li><a class="dropdown-item" href="/scripts/mecaniciens/paie.php"><i class="fas fa-wallet"></i> Paie</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid">
