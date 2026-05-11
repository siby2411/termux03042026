<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Travaux de Fin d'Exercice (TFE)";
$page_icon = "calendar-check";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des soldes par classe
$soldes = [];
$classes = [
    1 => 'Capitaux propres', 2 => 'Immobilisations', 3 => 'Stocks',
    4 => 'Tiers', 5 => 'Trésorerie', 6 => 'Charges', 7 => 'Produits'
];

foreach($classes as $classe => $libelle) {
    $debut = $classe * 100;
    $fin = ($classe * 100) + 99;
    
    $stmt_debit = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN ? AND ? AND YEAR(date_ecriture) = ?");
    $stmt_debit->execute([$debut, $fin, $exercice]);
    $total_debit[$classe] = $stmt_debit->fetchColumn();
    
    $stmt_credit = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN ? AND ? AND YEAR(date_ecriture) = ?");
    $stmt_credit->execute([$debut, $fin, $exercice]);
    $total_credit[$classe] = $stmt_credit->fetchColumn();
    
    $soldes[$classe] = ($classe <= 5) ? $total_debit[$classe] - $total_credit[$classe] : $total_credit[$classe] - $total_debit[$classe];
}

// Vérification de l'équilibre
$total_actif = $soldes[2] + $soldes[3] + $soldes[4] + $soldes[5];
$total_passif = $soldes[1] + $soldes[6] + $soldes[7];
$equilibre = abs($total_actif - $total_passif) < 1;
?>

<div class="row">
    <div class="col-md-12">
        <!-- En-tête -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calendar-check"></i> Travaux de Fin d'Exercice (TFE) - <?= $exercice ?></h5>
                <small>Principe d'indépendance des exercices - Image fidèle</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📖 Objectifs des TFE :</strong>
                    <ul class="mb-0">
                        <li>Assurer le respect du principe d'indépendance des exercices</li>
                        <li>Garantir l'image fidèle du patrimoine et du résultat</li>
                        <li>Intégrer uniquement les produits et charges de l'exercice clos</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tableau de bord TFE -->
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="card bg-primary text-white text-center"><div class="card-body"><h4><?= number_format($total_actif, 0, ',', ' ') ?> F</h4><small>Total Actif</small></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-white text-center"><div class="card-body"><h4><?= number_format($total_passif, 0, ',', ' ') ?> F</h4><small>Total Passif</small></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-white text-center"><div class="card-body"><h4><?= number_format($soldes[6] - $soldes[7], 0, ',', ' ') ?> F</h4><small>Résultat net</small></div></div></div>
            <div class="col-md-3"><div class="card bg-warning text-dark text-center"><div class="card-body"><?= $equilibre ? '<span class="badge bg-success fs-4">✓ ÉQUILIBRÉ</span>' : '<span class="badge bg-danger fs-4">✗ NON ÉQUILIBRÉ</span>' ?></div></div></div>
        </div>

        <!-- Navigation TFE -->
        <ul class="nav nav-tabs" id="tfeTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#regularisations">🔄 Régularisations</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#amortissements">📉 Amortissements</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#provisions">🛡️ Provisions</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventaire">📦 Inventaire</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#report">📋 Report à nouveau</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#etats">📊 États financiers</button></li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Onglet Régularisations -->
            <div class="tab-pane fade show active" id="regularisations">
                <div class="card">
                    <div class="card-header bg-secondary text-white">Écritures de régularisation de fin d'exercice</div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <strong>✅ Charges constatées d'avance (compte 481)</strong><br>
                            <code>Débit 481 / Crédit compte de charge</code>
                        </div>
                        <div class="alert alert-warning">
                            <strong>⚠️ Charges à payer (compte 483)</strong><br>
                            <code>Débit compte de charge / Crédit 483</code>
                        </div>
                        <div class="alert alert-info">
                            <strong>📌 Factures non parvenues (compte 408)</strong><br>
                            <code>Débit compte de charge / Débit 4454 / Crédit 408</code>
                        </div>
                        <form method="POST" action="ajouter_regularisation.php" class="row g-3">
                            <div class="col-md-3"><label>Type</label><select name="type_regul" class="form-select"><option value="CHARGE_CONSTATE_AVANCE">Charge constatée d'avance</option><option value="CHARGES_A_PAYER">Charge à payer</option><option value="PRODUITS_A_RECEVOIR">Produit à recevoir</option></select></div>
                            <div class="col-md-4"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                            <div class="col-md-2"><label>Montant (F)</label><input type="number" name="montant" class="form-control" required></div>
                            <div class="col-md-3"><button type="submit" class="btn-omega mt-4">Ajouter régularisation</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Onglet Amortissements -->
            <div class="tab-pane fade" id="amortissements">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Dotations aux amortissements</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Immobilisation</th><th>Valeur brute</th><th>Taux</th><th>Annuité</th><th>Amort. cumulé</th><th>VNC</th></tr></thead>
                                <tbody><?php
                                $amortissements = $pdo->query("SELECT * FROM AMORTISSEMENTS")->fetchAll();
                                foreach($amortissements as $a):
                                    $annuite = $a['valeur_originale'] * $a['taux'] / 100;
                                ?>
                                    <tr>
                                        <td><?= $a['libelle'] ?></td>
                                        <td class="text-end"><?= number_format($a['valeur_originale'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= $a['taux'] ?>%</td>
                                        <td class="text-end"><?= number_format($annuite, 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($a['amortissement_cumule'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end fw-bold"><?= number_format($a['valeur_originale'] - $a['amortissement_cumule'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                <?php endforeach; ?></tbody>
                            </table>
                        </div>
                        <a href="amortissements_complet.php" class="btn btn-warning mt-3">Gérer les amortissements</a>
                    </div>
                </div>
            </div>

            <!-- Onglet Provisions -->
            <div class="tab-pane fade" id="provisions">
                <div class="card">
                    <div class="card-header bg-danger text-white">Provisions et dépréciations</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th>Compte D</th><th>Compte C</th><th class="text-end">Montant</th></tr></thead>
                                <tbody><?php
                                $provisions = $pdo->query("SELECT * FROM PROVISIONS_DEPRECIATIONS WHERE statut = 'ACTIVE'")->fetchAll();
                                foreach($provisions as $p): ?>
                                    <tr>
                                        <td><?= $p['date_constitution'] ?> </td>
                                        <td><?= $p['libelle'] ?> </td>
                                        <td class="text-center"><?= $p['compte_dotation'] ?> </td>
                                        <td class="text-center"><?= $p['compte_provision'] ?> </td>
                                        <td class="text-end"><?= number_format($p['montant_actuel'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                <?php endforeach; ?></tbody>
                            </table>
                        </div>
                        <a href="gestion_provisions.php" class="btn btn-danger mt-3">Gérer les provisions</a>
                    </div>
                </div>
            </div>

            <!-- Onglet Inventaire -->
            <div class="tab-pane fade" id="inventaire">
                <div class="card">
                    <div class="card-header bg-info text-white">Inventaire physique des stocks</div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>📊 Principe :</strong> Stock final = Stock initial + Entrées - Sorties
                        </div>
                        <a href="inventaire_physique.php" class="btn btn-info">Lancer l'inventaire physique</a>
                    </div>
                </div>
            </div>

            <!-- Onglet Report à nouveau -->
            <div class="tab-pane fade" id="report">
                <div class="card">
                    <div class="card-header bg-success text-white">Report à nouveau (Classe 11)</div>
                    <div class="card-body">
                        <?php
                        $resultat = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799 AND YEAR(date_ecriture) = ?");
                        $resultat->execute([$exercice]);
                        $produits = $resultat->fetchColumn();
                        $resultat2 = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699 AND YEAR(date_ecriture) = ?");
                        $resultat2->execute([$exercice]);
                        $charges = $resultat2->fetchColumn();
                        $resultat_net = $produits - $charges;
                        ?>
                        <div class="alert <?= $resultat_net >= 0 ? 'alert-success' : 'alert-danger' ?>">
                            <strong>Résultat de l'exercice <?= $exercice ?> :</strong>
                            <h3><?= number_format(abs($resultat_net), 0, ',', ' ') ?> FCFA <?= $resultat_net >= 0 ? '(BÉNÉFICE)' : '(PERTE)' ?></h3>
                        </div>
                        <a href="report_nouveau.php" class="btn btn-success">Effectuer le report à nouveau</a>
                    </div>
                </div>
            </div>

            <!-- Onglet États financiers -->
            <div class="tab-pane fade" id="etats">
                <div class="card">
                    <div class="card-header bg-dark text-white">États financiers de synthèse (SYSCOHADA)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"><a href="bilan.php" class="btn btn-outline-primary w-100 mb-2">📊 Bilan</a></div>
                            <div class="col-md-6"><a href="compte_resultat.php" class="btn btn-outline-primary w-100 mb-2">📈 Compte de résultat (CPC)</a></div>
                            <div class="col-md-6"><a href="flux_tresorerie.php" class="btn btn-outline-primary w-100 mb-2">💵 Tableau des flux de trésorerie (TFT)</a></div>
                            <div class="col-md-6"><a href="variation_capitaux.php" class="btn btn-outline-primary w-100 mb-2">📉 Variation des capitaux propres (TVCP)</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Écritures de clôture -->
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">Écritures de clôture</div>
            <div class="card-body">
                <button class="btn btn-danger" onclick="if(confirm('⚠️ Confirmer la clôture définitive de l\'exercice ?')) window.location.href='cloture_exercice_action.php'">
                    <i class="bi bi-lock"></i> Clôturer l'exercice <?= $exercice ?>
                </button>
                <button class="btn btn-warning" onclick="if(confirm('⚠️ Confirmer la contrepassation des régularisations ?')) window.location.href='contrepasser_toutes.php'">
                    <i class="bi bi-arrow-repeat"></i> Contrepasser toutes les régularisations
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
