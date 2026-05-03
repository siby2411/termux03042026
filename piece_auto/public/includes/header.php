<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'PieceAuto ERP' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f4f7f6; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #2c3e50; color: white; transition: all 0.3s; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 12px 20px; display: block; transition: 0.3s; border-left: 4px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; border-left-color: #3498db; }
        .main-content { margin-left: var(--sidebar-width); padding: 20px; }
        .nav-header { padding: 20px; background: #1a252f; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="nav-header">
        <i class="fas fa-tools me-2"></i> OMEGA TECH
    </div>
    <div class="py-3">
        <a href="/index.php" class="<?= $page_title == 'Dashboard' ? 'active' : '' ?>"><i class="fas fa-home me-2"></i> Tableau de Bord</a>
        
        <div class="px-3 small text-uppercase text-muted mt-3 mb-2">Ventes</div>
        <a href="/modules/creation_vente.php"><i class="fas fa-plus-circle me-2"></i> Nouvelle Vente</a>
        <a href="/modules/gestion_commandes_vente.php"><i class="fas fa-history me-2"></i> Historique Ventes</a>
        <a href="/modules/gestion_clients.php"><i class="fas fa-users me-2"></i> Clients</a>

        <div class="px-3 small text-uppercase text-muted mt-3 mb-2">Logistique</div>
        <a href="/modules/gestion_stock.php"><i class="fas fa-boxes me-2"></i> État du Stock</a>
        <a href="/modules/creation_commande_achat.php"><i class="fas fa-truck me-2"></i> Achats Fournisseurs</a>
        <a href="/modules/tracabilite_vin.php"><i class="fas fa-search me-2"></i> Recherche VIN</a>

        <div class="px-3 small text-uppercase text-muted mt-3 mb-2">Système</div>
        <a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
    </div>
</div>

<div class="main-content">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4 rounded">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><?= $page_title ?></span>
            <div class="ms-auto text-muted">
                <i class="fas fa-user-circle me-1"></i> <?= $_SESSION['user_name'] ?? 'Admin' ?>
            </div>
        </div>
    </nav>
