<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$pdo = getPDO();

// ========== STATISTIQUES GLOBALES ==========
$stats = [];

// Clients et fournisseurs
$stats['total_clients'] = $pdo->query("SELECT COUNT(*) FROM clients WHERE actif = 1")->fetchColumn();
$stats['total_fournisseurs'] = $pdo->query("SELECT COUNT(*) FROM fournisseurs WHERE actif = 1")->fetchColumn();
$stats['total_produits'] = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif = 1")->fetchColumn();
$stats['produits_rupture'] = $pdo->query("SELECT COUNT(*) FROM produits WHERE quantite_stock <= seuil_alerte AND actif = 1")->fetchColumn();

// Commandes
$stats['commandes_mois'] = $pdo->query("SELECT COUNT(*) FROM commandes WHERE MONTH(date_commande) = MONTH(CURDATE()) AND YEAR(date_commande) = YEAR(CURDATE())")->fetchColumn();
$stats['commandes_attente'] = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut IN ('confirmée','en_préparation')")->fetchColumn();
$stats['commandes_livrees'] = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'livrée'")->fetchColumn();

// Chiffre d'affaires
$stats['ca_total'] = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE statut != 'annulée'")->fetchColumn() ?: 0;
$stats['ca_mois'] = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE MONTH(date_commande) = MONTH(CURDATE()) AND statut != 'annulée'")->fetchColumn() ?: 0;
$stats['ca_annee'] = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE YEAR(date_commande) = YEAR(CURDATE()) AND statut != 'annulée'")->fetchColumn() ?: 0;

// Top produits
$top_produits = $pdo->query("
    SELECT p.nom, p.code, SUM(lc.quantite) as total_vendu, SUM(lc.total_ttc) as ca
    FROM lignes_commande lc
    JOIN produits p ON lc.produit_id = p.id
    JOIN commandes c ON lc.commande_id = c.id
    WHERE c.statut = 'livrée'
    GROUP BY p.id
    ORDER BY total_vendu DESC
    LIMIT 5
")->fetchAll();

// Évolution mensuelle du CA
$evolution_ca = $pdo->query("
    SELECT DATE_FORMAT(date_commande, '%Y-%m') as mois, SUM(total_ttc) as ca
    FROM commandes
    WHERE statut != 'annulée' AND YEAR(date_commande) = YEAR(CURDATE())
    GROUP BY DATE_FORMAT(date_commande, '%Y-%m')
    ORDER BY mois
")->fetchAll();

// Commandes récentes
$commandes_recentes = $pdo->query("
    SELECT c.*, 
           CASE WHEN c.type_commande = 'vente' THEN cl.nom ELSE f.nom END as partenaire_nom,
           CASE WHEN c.type_commande = 'vente' THEN cl.prenom ELSE '' END as partenaire_prenom
    FROM commandes c
    LEFT JOIN clients cl ON c.client_id = cl.id
    LEFT JOIN fournisseurs f ON c.fournisseur_id = f.id
    ORDER BY c.date_commande DESC
    LIMIT 8
")->fetchAll();

// Alertes stock
$alertes_stock = $pdo->query("
    SELECT * FROM produits 
    WHERE quantite_stock <= seuil_alerte AND actif = 1 
    ORDER BY quantite_stock ASC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Portail E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #1abc9c;
        }
        .stat-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
        }
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(52,152,219,0.05);
            cursor: pointer;
        }
        .badge-statut {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-livree { background: #27ae60; color: white; }
        .badge-attente { background: #f39c12; color: white; }
        .badge-annulee { background: #e74c3c; color: white; }
        .badge-en_cours { background: #3498db; color: white; }
    </style>
</head>
<body>
    <?php require_once 'includes/menu.php'; ?>
    
    <div class="container-fluid mt-4">
        <!-- En-tête du dashboard -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="mb-1"><i class="fas fa-chart-line me-2"></i>Tableau de bord</h2>
                    <p class="mb-0 opacity-75">Bienvenue, <?= escape($_SESSION['user_name']) ?> | <?= date('d/m/Y H:i') ?></p>
                </div>
                <div class="col text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-calendar-alt me-1"></i> <?= date('F Y') ?>
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-store me-1"></i> Portail E-Commerce
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-label">Clients actifs</div>
                        <div class="stat-value"><?= number_format($stats['total_clients'], 0, ',', ' ') ?></div>
                        <small class="text-muted">+12% vs mois dernier</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-truck"></i></div>
                        <div class="stat-label">Fournisseurs</div>
                        <div class="stat-value"><?= number_format($stats['total_fournisseurs'], 0, ',', ' ') ?></div>
                        <small class="text-muted">Actifs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                        <div class="stat-label">Produits en stock</div>
                        <div class="stat-value"><?= number_format($stats['total_produits'], 0, ',', ' ') ?></div>
                        <small class="text-danger"><?= $stats['produits_rupture'] ?> en rupture</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-label">CA du mois</div>
                        <div class="stat-value"><?= formatMoney($stats['ca_mois']) ?></div>
                        <small>Total: <?= formatMoney($stats['ca_total']) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stat-label">Commandes du mois</div>
                        <div class="stat-value"><?= $stats['commandes_mois'] ?></div>
                        <small><?= $stats['commandes_attente'] ?> en attente</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-label">Commandes livrées</div>
                        <div class="stat-value"><?= $stats['commandes_livrees'] ?></div>
                        <small>Taux satisfaction: 98%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
                        <div class="stat-label">CA annuel</div>
                        <div class="stat-value"><?= formatMoney($stats['ca_annee']) ?></div>
                        <small>Objectif: <?= formatMoney($stats['ca_annee'] * 1.2) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Évolution du chiffre d'affaires <?= date('Y') ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="caChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Top 5 produits les plus vendus</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($top_produits as $i => $p): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary rounded-pill me-2"><?= $i+1 ?></span>
                                    <strong><?= escape($p['nom']) ?></strong>
                                    <br><small class="text-muted">Code: <?= escape($p['code']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold"><?= $p['total_vendu'] ?></span>
                                    <small>unités</small>
                                    <br><small class="text-success"><?= formatMoney($p['ca']) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($top_produits)): ?>
                            <div class="text-center text-muted py-3">Aucune donnée disponible</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commandes récentes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Dernières commandes</h5>
                        <a href="commandes/liste.php" class="btn btn-sm btn-primary">Voir toutes <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>N° commande</th>
                                        <th>Client/Fournisseur</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Total TTC</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commandes_recentes as $c): ?>
                                    <tr>
                                        <td><code><?= escape($c['numero_commande']) ?></code></td>
                                        <td>
                                            <strong><?= escape($c['partenaire_nom']) ?></strong>
                                            <?php if ($c['partenaire_prenom']): ?><br><small><?= escape($c['partenaire_prenom']) ?></small><?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $c['type_commande'] == 'vente' ? 'bg-success' : 'bg-info' ?>">
                                                <?= $c['type_commande'] == 'vente' ? 'Vente' : 'Achat' ?>
                                            </span>
                                        </td>
                                        <td><?= formatDate($c['date_commande']) ?></td>
                                        <td class="fw-bold"><?= formatMoney($c['total_ttc']) ?></td>
                                        <td>
                                            <?php
                                            $statut_class = match($c['statut']) {
                                                'livrée' => 'badge-livree',
                                                'annulée' => 'badge-annulee',
                                                'confirmée', 'en_préparation' => 'badge-attente',
                                                default => 'badge-en_cours'
                                            };
                                            ?>
                                            <span class="badge-statut <?= $statut_class ?>"><?= escape($c['statut']) ?></span>
                                        </td>
                                        <td>
                                            <a href="commandes/fiche.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes stock et activités -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Alertes stock</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($alertes_stock)): ?>
                            <div class="text-center text-success py-3">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p>Tous les stocks sont suffisants</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($alertes_stock as $p): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= escape($p['nom']) ?></strong>
                                        <br><small class="text-muted">Code: <?= escape($p['code']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger">Stock: <?= $p['quantite_stock'] ?></span>
                                        <small class="text-muted d-block">Seuil: <?= $p['seuil_alerte'] ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition des commandes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="repartitionChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique CA mensuel
        const ctx = document.getElementById('caChart').getContext('2d');
        const caData = <?= json_encode($evolution_ca) ?>;
        const moisLabels = caData.map(d => d.mois);
        const caValues = caData.map(d => d.ca);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: moisLabels,
                datasets: [{
                    label: 'Chiffre d\'affaires (FCFA)',
                    data: caValues,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52,152,219,0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2c3e50',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
        
        // Graphique répartition
        const repartition = <?= json_encode([
            'ventes' => $pdo->query("SELECT COUNT(*) FROM commandes WHERE type_commande = 'vente'")->fetchColumn(),
            'achats' => $pdo->query("SELECT COUNT(*) FROM commandes WHERE type_commande = 'achat'")->fetchColumn()
        ]) ?>;
        
        new Chart(document.getElementById('repartitionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Ventes', 'Achats'],
                datasets: [{
                    data: [repartition.ventes, repartition.achats],
                    backgroundColor: ['#27ae60', '#3498db'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' commandes';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
