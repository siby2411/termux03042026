<?php
require_once '../../core/Auth.php';
require_once '../../core/Database.php';
Auth::check();

$stocks = Database::query("SELECT id, denomination, stock_actuel, stock_min, prix_vente_ttc FROM medicaments WHERE actif=1 ORDER BY stock_actuel ASC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion du Stock | PharmaSen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="bi bi-boxes text-success"></i> État des Stocks</h2>
            <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimer Inventaire</button>
        </div>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Médicament</th>
                            <th>Prix Vente</th>
                            <th class="text-center">Stock Actuel</th>
                            <th>Seuil Alerte</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stocks as $s): ?>
                        <tr>
                            <td class="fw-bold"><?= $s['denomination'] ?></td>
                            <td><?= number_format($s['prix_vente_ttc'], 0, ',', ' ') ?> F</td>
                            <td class="text-center">
                                <span class="h5 fw-bold <?= $s['stock_actuel'] <= $s['stock_min'] ? 'text-danger' : 'text-success' ?>">
                                    <?= $s['stock_actuel'] ?>
                                </span>
                            </td>
                            <td><?= $s['stock_min'] ?></td>
                            <td>
                                <?php if($s['stock_actuel'] <= 0): ?>
                                    <span class="badge bg-danger">Rupture</span>
                                <?php elseif($s['stock_actuel'] <= $s['stock_min']): ?>
                                    <span class="badge bg-warning text-dark">Critique</span>
                                <?php else: ?>
                                    <span class="badge bg-success">OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
