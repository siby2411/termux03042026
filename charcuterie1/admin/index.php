<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

requireLogin();

$pdo = getPDO();

// Statistiques avec les bonnes tables
$total_produits = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_fournisseurs = $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn();

// Pour les commandes, vérifier si la table existe
$total_commandes = 0;
$ca_mois = 0;
$commandes_jour = 0;

try {
    // Vérifier si la table ventes existe (au lieu de commandes)
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('ventes', $tables)) {
        $total_commandes = $pdo->query("SELECT COUNT(*) FROM ventes")->fetchColumn();
        $commandes_jour = $pdo->query("SELECT COUNT(*) FROM ventes WHERE DATE(date_vente) = CURDATE()")->fetchColumn();
        $ca_mois = $pdo->query("SELECT SUM(total_ttc) FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE()) AND YEAR(date_vente) = YEAR(CURDATE())")->fetchColumn();
    } elseif (in_array('commandes', $tables)) {
        $total_commandes = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
        $commandes_jour = $pdo->query("SELECT COUNT(*) FROM commandes WHERE DATE(date_commande) = CURDATE()")->fetchColumn();
        $ca_mois = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE MONTH(date_commande) = MONTH(CURDATE()) AND YEAR(date_commande) = YEAR(CURDATE())")->fetchColumn();
    }
} catch (Exception $e) {
    // Ignorer les erreurs
}

// Alertes stock
$alertes_stock = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel <= stock_min")->fetchColumn();

// Dernières ventes
$dernieres_ventes = [];
try {
    if (in_array('ventes', $tables)) {
        $dernieres_ventes = $pdo->query("
            SELECT v.*, c.nom as client_nom 
            FROM ventes v 
            LEFT JOIN clients c ON v.client_id = c.id 
            ORDER BY v.date_vente DESC 
            LIMIT 5
        ")->fetchAll();
    }
} catch (Exception $e) {
    // Ignorer
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Administration Charcuterie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            transition: transform 0.2s;
            border-radius: 15px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .bg-primary-gradient { background: linear-gradient(135deg, #3498db, #2980b9); }
        .bg-success-gradient { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-warning-gradient { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .bg-info-gradient { background: linear-gradient(135deg, #1abc9c, #16a085); }
        .bg-danger-gradient { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .bg-secondary-gradient { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="fas fa-utensils me-2"></i>OMEGA Charcuterie</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li class="nav-item"><a class="nav-link" href="produits.php"><i class="fas fa-boxes"></i> Produits</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-tags"></i> Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="ventes.php"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
                    <li class="nav-item"><a class="nav-link" href="clients.php"><i class="fas fa-users"></i> Clients</a></li>
                    <li class="nav-item"><a class="nav-link" href="fournisseurs.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="stock.php"><i class="fas fa-warehouse"></i> Stock</a></li>
                </ul>
                <span class="navbar-text me-3">
                    <i class="fas fa-user-circle"></i> <?= escape($_SESSION['admin_nom'] ?? 'Admin') ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-chart-line me-2"></i>Tableau de bord</h2>
                <p class="text-muted">Bienvenue dans l'interface d'administration - <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <!-- Cartes KPI -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Produits</h6>
                                <h2 class="stat-value mb-0"><?= $total_produits ?></h2>
                                <small>En catalogue</small>
                            </div>
                            <i class="fas fa-boxes fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Commandes/Ventes</h6>
                                <h2 class="stat-value mb-0"><?= $total_commandes ?></h2>
                                <small>Total ventes</small>
                            </div>
                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Clients</h6>
                                <h2 class="stat-value mb-0"><?= $total_clients ?></h2>
                                <small>Clients enregistrés</small>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Fournisseurs</h6>
                                <h2 class="stat-value mb-0"><?= $total_fournisseurs ?></h2>
                                <small>Partenaires</small>
                            </div>
                            <i class="fas fa-truck fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-secondary-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Ventes aujourd'hui</h6>
                                <h2 class="stat-value mb-0"><?= $commandes_jour ?></h2>
                                <small>Nouvelles ventes</small>
                            </div>
                            <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-danger-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Alertes stock</h6>
                                <h2 class="stat-value mb-0"><?= $alertes_stock ?></h2>
                                <small>Produits en rupture</small>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-primary-gradient text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">CA du mois</h6>
                                <h2 class="stat-value mb-0"><?= formatMoney($ca_mois) ?></h2>
                                <small>Chiffre d'affaires</small>
                            </div>
                            <i class="fas fa-chart-line fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="produits.php?action=ajouter" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus me-2"></i>Ajouter un produit
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="ventes.php?action=nouvelle" class="btn btn-outline-success w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Nouvelle vente
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="stock.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-warehouse me-2"></i>Gérer le stock
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="rapports.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-chart-line me-2"></i>Voir les rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières ventes -->
        <?php if (!empty($dernieres_ventes)): ?>
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Dernières ventes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                              <tr><th>N° vente</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dernieres_ventes as $v): ?>
                            <tr>
                                <td><code><?= escape($v['numero_vente'] ?? $v['id']) ?></code></td>
                                <td><?= escape($v['client_nom'] ?? '-') ?></td>
                                <td><?= formatDate($v['date_vente']) ?></td>
                                <td><?= formatMoney($v['total_ttc']) ?></td>
                                <td><span class="badge bg-success"><?= escape($v['statut'] ?? 'Terminée') ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
