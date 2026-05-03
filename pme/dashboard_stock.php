<?php
include 'includes/db.php';
include 'includes/header.php';

// Analyse des données
$total_articles = $pdo->query("SELECT SUM(stock_actuel) FROM produits")->fetchColumn();
$valeur_stock = $pdo->query("SELECT SUM(stock_actuel * prix_unitaire) FROM produits")->fetchColumn();
$alertes = $pdo->query("SELECT * FROM produits WHERE stock_actuel <= seuil_alerte")->fetchAll();
$mouvements = $pdo->query("SELECT l.*, p.designation FROM stock_logs l JOIN produits p ON l.produit_id = p.id ORDER BY date_log DESC LIMIT 10")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <small class="text-uppercase opacity-75">Valeur Totale du Stock</small>
<a href="export_inventaire.php" target="_blank" class="btn btn-sm btn-outline-light mb-2"><i class="fas fa-file-pdf me-2"></i>Exporter PDF pour Comptable</a>
                <h2 class="fw-bold"><?= number_format($valeur_stock, 2, ',', ' ') ?> €</h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-dark text-white p-3">
                <small class="text-uppercase opacity-75">Articles en Entrepôt</small>
<a href="export_inventaire.php" target="_blank" class="btn btn-sm btn-outline-light mb-2"><i class="fas fa-file-pdf me-2"></i>Exporter PDF pour Comptable</a>
                <h2 class="fw-bold"><?= $total_articles ?> unités</h2>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Niveaux de Stock (Jauges)</div>
                <div class="card-body">
                    <?php 
                    $prods = $pdo->query("SELECT designation, stock_actuel, seuil_alerte FROM produits LIMIT 8")->fetchAll();
                    foreach($prods as $pr): 
                        $percent = min(100, ($pr['stock_actuel'] / ($pr['seuil_alerte'] * 4)) * 100);
                        $color = ($pr['stock_actuel'] <= $pr['seuil_alerte']) ? 'bg-danger' : 'bg-success';
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= $pr['designation'] ?></span>
                            <span class="fw-bold"><?= $pr['stock_actuel'] ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar <?= $color ?>" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white fw-bold">Alertes de Rupture</div>
                <div class="list-group list-group-flush">
                    <?php foreach($alertes as $a): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $a['designation'] ?>
                        <span class="badge bg-danger">Reste : <?= $a['stock_actuel'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
