<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';

$dbObj = new Database();
$pdo = $dbObj->getConnection();

// Initialisation des compteurs
$dep = [
    'lavage' => ['rev' => 0, 'chg' => 0],
    'atelier' => ['rev' => 0, 'chg' => 0],
    'magasin' => ['rev' => 0, 'chg' => 0]
];

try {
    // 1. DÉPARTEMENT LAVAGE
    $res = $pdo->query("SELECT SUM(montant_encaisse) as total FROM lavage_operations WHERE statut='Terminé'")->fetch();
    $dep['lavage']['rev'] = $res['total'] ?? 0;

    // 2. DÉPARTEMENT RÉPARATION (ATELIER)
    $res = $pdo->query("SELECT SUM(main_doeuvre) as total FROM interventions WHERE statut='Terminé'")->fetch();
    $dep['atelier']['rev'] = $res['total'] ?? 0;

    // 3. DÉPARTEMENT MAGASIN (VENTE PIÈCES)
    $res = $pdo->query("SELECT SUM(prix_vente) as total FROM pieces_detachees")->fetch();
    $dep['magasin']['rev'] = $res['total'] ?? 0;

    // 4. RÉPARTITION DES CHARGES
    $charges = $pdo->query("SELECT categorie, SUM(montant) as total FROM charges_exploitation GROUP BY categorie")->fetchAll(PDO::FETCH_ASSOC);
    foreach($charges as $c) {
        if(isset($dep[strtolower($c['categorie'])])) {
            $dep[strtolower($c['categorie'])]['chg'] = $c['total'];
        }
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erreur de calcul : " . $e->getMessage() . "</div>";
}

$totalRev = $dep['lavage']['rev'] + $dep['atelier']['rev'] + $dep['magasin']['rev'];
$totalChg = $dep['lavage']['chg'] + $dep['atelier']['chg'] + $dep['magasin']['chg'];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-chart-line text-primary"></i> TABLEAU DE BORD MULTI-DÉPARTEMENTS</h2>
        <span class="badge bg-dark p-2"><?= date('d/m/Y H:i') ?></span>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white fw-bold"><i class="fas fa-wrench"></i> DÉPT. RÉPARATION</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Revenus (MO) :</span> <span class="fw-bold"><?= number_format($dep['atelier']['rev'], 0, ',', ' ') ?></span></div>
                    <div class="d-flex justify-content-between text-danger"><span>Charges :</span> <span>- <?= number_format($dep['atelier']['chg'], 0, ',', ' ') ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold text-success"><span>Marge :</span> <span><?= number_format($dep['atelier']['rev'] - $dep['atelier']['chg'], 0, ',', ' ') ?> FCFA</span></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white fw-bold"><i class="fas fa-boxes"></i> DÉPT. MAGASIN</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Ventes Pièces :</span> <span class="fw-bold"><?= number_format($dep['magasin']['rev'], 0, ',', ' ') ?></span></div>
                    <div class="d-flex justify-content-between text-danger"><span>Charges :</span> <span>- <?= number_format($dep['magasin']['chg'], 0, ',', ' ') ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold text-success"><span>Marge :</span> <span><?= number_format($dep['magasin']['rev'] - $dep['magasin']['chg'], 0, ',', ' ') ?> FCFA</span></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white fw-bold"><i class="fas fa-soap"></i> DÉPT. LAVAGE</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between"><span>Prestations :</span> <span class="fw-bold"><?= number_format($dep['lavage']['rev'], 0, ',', ' ') ?></span></div>
                    <div class="d-flex justify-content-between text-danger"><span>Charges :</span> <span>- <?= number_format($dep['lavage']['chg'], 0, ',', ' ') ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold text-success"><span>Marge :</span> <span><?= number_format($dep['lavage']['rev'] - $dep['lavage']['chg'], 0, ',', ' ') ?> FCFA</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 bg-light">
        <div class="card-body">
            <h4 class="fw-bold text-center mb-4">RÉSULTAT CONSOLIDÉ OMEGA GARAGE</h4>
            <div class="row text-center">
                <div class="col-md-4">
                    <h6 class="text-muted">TOTAL REVENUS</h6>
                    <h2 class="text-primary fw-bold"><?= number_format($totalRev, 0, ',', ' ') ?> <small>FCFA</small></h2>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">TOTAL CHARGES</h6>
                    <h2 class="text-danger fw-bold"><?= number_format($totalChg, 0, ',', ' ') ?> <small>FCFA</small></h2>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">BÉNÉFICE NET GLOBAL</h6>
                    <h2 class="text-dark fw-bold" style="border-bottom: 4px double #000; display: inline-block;">
                        <?= number_format($totalRev - $totalChg, 0, ',', ' ') ?> FCFA
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
