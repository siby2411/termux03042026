<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA ERP - Gestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { width: 250px; height: 100vh; position: fixed; background: #1e293b; color: white; overflow-y: auto; }
        .sidebar a { color: #94a3b8; text-decoration: none; padding: 12px 20px; display: block; font-size: 0.9rem; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #334155; color: white; border-left: 4px solid #38bdf8; }
        .main-content { margin-left: 250px; padding: 25px; }
        .small { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05rem; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="p-3 fw-bold text-center text-primary border-bottom border-secondary mb-2">OMEGA TECH</div>

    <div class="px-3 small text-muted mt-3">TABLEAU DE BORD</div>
    <a href="/modules/tableau_de_bord.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>

    <div class="px-3 small text-muted mt-3">TIERS</div>
    <a href="/modules/gestion_clients.php"><i class="fas fa-users me-2"></i> État Clients</a>
    <a href="/modules/gestion_fournisseurs.php"><i class="fas fa-truck me-2"></i> État Fournisseurs</a>

    <div class="px-3 small text-muted mt-3">VENTES</div>
    <a href="/modules/creation_vente.php"><i class="fas fa-plus me-2"></i> Nouvelle Vente</a>
    <a href="/modules/gestion_ventes.php"><i class="fas fa-list me-2"></i> Historique Ventes</a>

    <div class="px-3 small text-muted mt-3">LOGISTIQUE</div>
    <a href="/modules/gestion_stock.php"><i class="fas fa-boxes me-2"></i> Liste État du Stock Recherche Pièces</a>
    <a href="/modules/ajouter_piece.php"><i class="fas fa-plus-circle me-2"></i> Ajouter une pièce</a>
    <a href="/modules/reception_achats.php"><i class="fas fa-file-import me-2"></i> Réception Achats</a>

    <div class="px-3 small text-muted mt-3">FINANCES</div>
    <a href="/modules/etat_financier_clients.php"><i class="fas fa-file-invoice-dollar me-2"></i> État Clients</a>
    <a href="/modules/historique_paiements.php"><i class="fas fa-history me-2"></i> Historique Paiements</a>

    <div class="px-3 small text-muted mt-3">SYSTÈME</div>
    <a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
</div>
<div class="main-content">
