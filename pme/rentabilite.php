<?php include 'includes/check_direction.php'; ?>
<?php
include 'includes/db.php';
include 'includes/header.php';

$prods = $pdo->query("SELECT designation, prix_unitaire as prix_vente, prix_achat_moyen, stock_actuel FROM produits WHERE prix_achat_moyen > 0")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-hand-holding-usd text-success me-2"></i>Analyse de la Rentabilité Net</h2>
        <div class="text-muted small text-end">Basé sur le Prix Moyen Pondéré (PMP)</div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Désignation Produit</th>
                        <th>Prix d'Achat (PMP)</th>
                        <th>Prix de Vente HT</th>
                        <th>Marge Brute / Unité</th>
                        <th>Taux de Marge (%)</th>
                        <th>Valeur Marge Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($prods as $p): 
                        $marge = $p['prix_vente'] - $p['prix_achat_moyen'];
                        $taux = ($p['prix_vente'] > 0) ? ($marge / $p['prix_vente']) * 100 : 0;
                        $color = ($taux < 15) ? 'text-danger' : (($taux < 30) ? 'text-warning' : 'text-success');
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($p['designation']) ?></td>
                        <td><?= number_format($p['prix_achat_moyen'], 2) ?> €</td>
                        <td><?= number_format($p['prix_vente'], 2) ?> €</td>
                        <td class="fw-bold <?= $color ?>">+ <?= number_format($marge, 2) ?> €</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar <?= str_replace('text', 'bg', $color) ?>" role="progressbar" style="width: <?= $taux ?>%"><?= round($taux) ?>%</div>
                            </div>
                        </td>
                        <td class="bg-light fw-bold"><?= number_format($marge * $p['stock_actuel'], 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <h5>Marge Potentielle Globale</h5>
                <?php
                $total_marge = 0;
                foreach($prods as $p) { $total_marge += ($p['prix_vente'] - $p['prix_achat_moyen']) * $p['stock_actuel']; }
                ?>
                <h2 class="mb-0"><?= number_format($total_marge, 2, ',', ' ') ?> €</h2>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
