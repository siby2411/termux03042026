<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Centres de coûts et rentabilité - Comptabilité analytique";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';
$period = $_GET['period'] ?? date('Y-m');

// Récupération des centres
$centres = $pdo->query("SELECT * FROM CENTRES_ANALYTIQUES ORDER BY type_centre, code")->fetchAll();

// Calcul des résultats par centre
$resultats = [];
$total_charges = 0;
$total_produits = 0;

foreach($centres as $c) {
    // Récupération des imputations
    $imputations = $pdo->prepare("
        SELECT i.*, e.compte_debite_id, e.compte_credite_id, e.montant
        FROM IMPUTATIONS_ANALYTIQUES i
        JOIN ECRITURES_COMPTABLES e ON i.ecriture_id = e.id
        WHERE i.centre_id = ? AND DATE_FORMAT(i.date_imputation, '%Y-%m') = ?
    ");
    $imputations->execute([$c['id'], $period]);
    $imput_data = $imputations->fetchAll();
    
    $charges = 0;
    $produits = 0;
    foreach($imput_data as $imp) {
        if($imp['compte_debite_id'] >= 60 && $imp['compte_debite_id'] <= 69) {
            $charges += $imp['montant_impute'];
        }
        if($imp['compte_credite_id'] >= 70 && $imp['compte_credite_id'] <= 79) {
            $produits += $imp['montant_impute'];
        }
    }
    
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

// Clés d'imputation
$cles_repartition = $pdo->query("SELECT COUNT(*) FROM IMPUTATIONS_ANALYTIQUES")->fetchColumn();
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart"></i> Comptabilité analytique - Centres de coûts/profits</h5>
                <small>Répartition des charges et produits pour analyse de rentabilité</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définitions :</strong><br>
                    - <strong>Centre de coûts</strong> : Service qui génère des charges (Administration, R&D)<br>
                    - <strong>Centre de profits</strong> : Service qui génère des revenus (Commercial, Production)<br>
                    - <strong>Erreur d'imputation</strong> : Affectation erronée d'une charge/produit à un mauvais centre<br>
                    - <strong>Contrepassation</strong> : Annulation d'une écriture pour la remplacer par une nouvelle
                </div>

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
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits, 0, ',', ' ') ?> F</h4>
                                <small>Total produits analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Total charges analytiques</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits - $total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Résultat analytique</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white text-center">
                            <div class="card-body">
                                <h4><?= $cles_repartition ?></h4>
                                <small>Clés d'imputation</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des centres -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Centre</th><th>Type</th><th class="text-end">Charges (F)</th>
                                <th class="text-end">Produits (F)</th><th class="text-end">Résultat (F)</th>
                                <th>Marge (%)</th><th>Performance</th>
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
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-<?= $r['marge'] >= 30 ? 'success' : ($r['marge'] >= 0 ? 'warning' : 'danger') ?>" 
                                             style="width: <?= min(100, max(0, $r['marge'])) ?>%">
                                        </div>
                                    </div>
                                    <?= number_format($r['marge'], 1) ?>%
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

                <!-- Explication erreur imputation vs contrepassation -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">❌ Erreur d'imputation</div>
                            <div class="card-body">
                                <p><strong>Définition :</strong> Une erreur d'imputation se produit lorsqu'une charge ou un produit est affecté à un mauvais centre analytique.</p>
                                <p><strong>Exemple :</strong> Une facture d'électricité de 100 000 F imputée au centre "Commercial" alors qu'elle concerne l'Administration.</p>
                                <p><strong>Correction :</strong> Il suffit de modifier la clé d'imputation dans le paramétrage.</p>
                                <div class="alert alert-light">
                                    <code>UPDATE IMPUTATIONS_ANALYTIQUES SET centre_id = 'ADM' WHERE centre_id = 'COMM' AND ecriture_id = X;</code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">🔄 Contrepassation</div>
                            <div class="card-body">
                                <p><strong>Définition :</strong> Une contrepassation est l'annulation d'une écriture comptable pour la remplacer par une nouvelle.</p>
                                <p><strong>Exemple :</strong> Erreur sur le compte comptable (débit sur 601 au lieu de 602).</p>
                                <p><strong>Correction :</strong> L'écriture originale est annulée (mêmes montants en sens inverse), puis une nouvelle correcte est saisie.</p>
                                <div class="alert alert-light">
                                    <code>INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, type_ecriture) VALUES (?, 'Contrepassation', 602, 601, X, 'CONTREPASSATION');</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-secondary mt-3">
                    <strong>📊 Mode de calcul :</strong>
                    <ul class="mb-0">
                        <li><strong>Charges par centre</strong> = Σ (Montant des écritures débit × % d'imputation)</li>
                        <li><strong>Produits par centre</strong> = Σ (Montant des écritures crédit × % d'imputation)</li>
                        <li><strong>Résultat analytique</strong> = Produits - Charges (par centre)</li>
                        <li><strong>Taux de marge</strong> = (Résultat / Produits) × 100</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
