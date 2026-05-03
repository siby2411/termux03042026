<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();

// Récupérer les charges
$charges = $pdo->query("SELECT * FROM charges ORDER BY date_charge DESC, created_at DESC")->fetchAll();

// Calculer le total par catégorie
$total_global = 0;
$total_par_categorie = [];

foreach ($charges as $c) {
    $total_global += $c['montant'];
    $cat = $c['categorie'] ?: 'Non catégorisé';
    if (!isset($total_par_categorie[$cat])) {
        $total_par_categorie[$cat] = 0;
    }
    $total_par_categorie[$cat] += $c['montant'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Charges - OMEGA Hôtel</title>
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
        .bg-gradient-electricite { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .bg-gradient-eau { background: linear-gradient(135deg, #3498db, #2980b9); }
        .bg-gradient-internet { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
        .bg-gradient-fournitures { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-gradient-entretien { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .bg-gradient-default { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
    </style>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line me-2"></i>Gestion des charges</h2>
            <a href="ajouter.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Nouvelle charge
            </a>
        </div>

        <!-- Carte total global -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <h6><i class="fas fa-money-bill-wave me-2"></i>Total des charges</h6>
                        <h3><?= formatMoney($total_global) ?></h3>
                        <small>Depuis le début</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes par catégorie -->
        <div class="row mb-4">
            <?php foreach ($total_par_categorie as $cat => $montant): 
                $gradient_class = 'bg-gradient-default';
                if (strpos($cat, 'Électricité') !== false) $gradient_class = 'bg-gradient-electricite';
                elseif (strpos($cat, 'Eau') !== false) $gradient_class = 'bg-gradient-eau';
                elseif (strpos($cat, 'Internet') !== false) $gradient_class = 'bg-gradient-internet';
                elseif (strpos($cat, 'Fournitures') !== false) $gradient_class = 'bg-gradient-fournitures';
                elseif (strpos($cat, 'Entretien') !== false) $gradient_class = 'bg-gradient-entretien';
            ?>
            <div class="col-md-3 mb-3">
                <div class="card stat-card <?= $gradient_class ?> text-white">
                    <div class="card-body">
                        <h6><i class="fas fa-folder me-2"></i><?= escape($cat) ?></h6>
                        <h4><?= formatMoney($montant) ?></h4>
                        <small><?= round(($montant / $total_global) * 100, 1) ?>% du total</small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Liste des charges -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Détail des charges</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                              <tr>
                                <th>Libellé</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Catégorie</th>
                                <th>Notes</th>
                                <th>Actions</th>
                              </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($charges as $c): ?>
                              <tr>
                                <td><strong><?= escape($c['libelle']) ?></strong></td>
                                <td class="fw-bold text-danger"><?= formatMoney($c['montant']) ?></td>
                                <td><?= formatDate($c['date_charge']) ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    if ($c['categorie'] == 'Électricité') $badge_class = 'warning';
                                    elseif ($c['categorie'] == 'Eau') $badge_class = 'info';
                                    elseif ($c['categorie'] == 'Internet') $badge_class = 'purple';
                                    elseif ($c['categorie'] == 'Fournitures') $badge_class = 'success';
                                    elseif ($c['categorie'] == 'Entretien') $badge_class = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge_class ?>">
                                        <?= escape($c['categorie'] ?: 'Non catégorisé') ?>
                                    </span>
                                </td>
                                <td><?= escape($c['notes']) ?></td>
                                <td>
                                    <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette charge ?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                            <?php if (empty($charges)): ?>
                              <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucune charge enregistrée
                                </td>
                              </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
