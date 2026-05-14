<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Centres de coûts et rentabilité - Comptabilité analytique";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$period = $_GET['period'] ?? date('Y-m');

// Récupération des centres analytiques
$centres = $pdo->query("SELECT * FROM CENTRES_ANALYTIQUES ORDER BY type_centre, code")->fetchAll();

// Calcul des résultats par centre pour la période sélectionnée
$resultats = [];
$total_charges = 0;
$total_produits = 0;

foreach($centres as $c) {
    // Récupération des charges (comptes 60-69)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) 
        FROM ECRITURES_COMPTABLES 
        WHERE section_analytique_id = ? 
        AND compte_debite_id BETWEEN 600 AND 699
        AND DATE_FORMAT(date_ecriture, '%Y-%m') = ?
    ");
    $stmt->execute([$c['id'], $period]);
    $charges = $stmt->fetchColumn();
    
    // Récupération des produits (comptes 70-79)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0) 
        FROM ECRITURES_COMPTABLES 
        WHERE section_analytique_id = ? 
        AND compte_credite_id BETWEEN 700 AND 799
        AND DATE_FORMAT(date_ecriture, '%Y-%m') = ?
    ");
    $stmt->execute([$c['id'], $period]);
    $produits = $stmt->fetchColumn();
    
    $resultats[$c['id']] = [
        'code' => $c['code'],
        'libelle' => $c['libelle'],
        'type' => $c['type_centre'],
        'charges' => $charges,
        'produits' => $produits,
        'resultat' => $produits - $charges,
        'marge' => $produits > 0 ? ($produits - $charges) / $produits * 100 : 0
    ];
    
    $total_charges += $charges;
    $total_produits += $produits;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart"></i> Centres de coûts et rentabilité</h5>
                <small>Analyse par centre analytique - Période: <?= date('F Y', strtotime($period . '-01')) ?></small>
            </div>
            <div class="card-body">
                
                <!-- Sélection période -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-auto">
                        <label>Période d'analyse</label>
                        <input type="month" name="period" class="form-control" value="<?= $period ?>">
                    </div>
                    <div class="col-auto" style="margin-top: 29px;">
                        <button type="submit" class="btn btn-primary">Analyser</button>
                    </div>
                </form>

                <!-- KPIs -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Total charges analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits, 0, ',', ' ') ?> F</h4>
                                <small>Total produits analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits - $total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Résultat analytique</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des résultats -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Centre</th><th>Type</th><th class="text-end">Charges (F)</th>
                                <th class="text-end">Produits (F)</th><th class="text-end">Résultat (F)</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resultats as $r): ?>
                            <tr>
                                <td><strong><?= $r['code'] ?></strong> - <?= $r['libelle'] ?> </td>
                                <td class="text-center">
                                    <span class="badge <?= $r['type'] == 'PROFIT' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $r['type'] ?>
                                    </span>
                                  </td>
                                <td class="text-end text-danger"><?= number_format($r['charges'], 0, ',', ' ') ?> F</td>
                                <td class="text-end text-success"><?= number_format($r['produits'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $r['resultat'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($r['resultat'], 0, ',', ' ') ?> F
                                  </td>
                                <td class="text-center">
                                    <?php if($r['resultat'] > 500000): ?>
                                        <span class="badge bg-success">🏆 Excellence</span>
                                    <?php elseif($r['resultat'] > 0): ?>
                                        <span class="badge bg-info">📈 Rentable</span>
                                    <?php elseif($r['resultat'] == 0): ?>
                                        <span class="badge bg-secondary">⚖️ Équilibre</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">⚠️ Déficitaire</span>
                                    <?php endif; ?>
                                  </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Exemple de charge existante -->
                <div class="alert alert-info mt-3">
                    <strong>📊 Donnée analytique existante :</strong><br>
                    Centre <strong>PROD (Production)</strong> a une charge de <strong>15 000 F</strong> enregistrée.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
