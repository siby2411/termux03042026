<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse financière - Seuil de rentabilité, EBE, CAF";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des données CG
$ca = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799 AND YEAR(date_ecriture) = 2026")->fetchColumn();
$achats = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 601 AND YEAR(date_ecriture) = 2026")->fetchColumn();
$salaires = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 641 AND YEAR(date_ecriture) = 2026")->fetchColumn();
$services = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 613 AND YEAR(date_ecriture) = 2026")->fetchColumn();
$dotations = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 681 AND YEAR(date_ecriture) = 2026")->fetchColumn();
$charges_fin = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 671 AND YEAR(date_ecriture) = 2026")->fetchColumn();

// Définition des coûts fixes et variables (exemple)
$cout_fixe = $salaires + $services + $dotations + $charges_fin;
$cout_variable = $achats;
$marge_sur_cout_variable = $ca - $cout_variable;
$taux_marge = $ca > 0 ? ($marge_sur_cout_variable / $ca) * 100 : 0;

// Seuil de rentabilité (Chiffre d'affaires critique)
$seuil_rentabilite = $taux_marge > 0 ? ($cout_fixe / ($taux_marge / 100)) : 0;

// Marge de sécurité
$marge_securite = $ca - $seuil_rentabilite;
$indice_securite = $ca > 0 ? ($marge_securite / $ca) * 100 : 0;

// Levier opérationnel
$resultat = $ca - $cout_fixe - $cout_variable;
$levier_operationnel = $resultat != 0 ? $marge_sur_cout_variable / $resultat : 0;

// EBE (Excédent Brut d'Exploitation)
$ebe = $ca - $cout_variable - ($salaires + $services);

// CAF (Capacité d'Autofinancement)
$caf = $ebe - $charges_fin;

// Scénarios pour avenir aléatoire
$scenarios = [
    'OPTIMISTE' => ['probabilite' => 30, 'ca' => $ca * 1.2, 'cv' => $cout_variable * 1.15, 'cf' => $cout_fixe * 1.05],
    'REALISTE' => ['probabilite' => 50, 'ca' => $ca, 'cv' => $cout_variable, 'cf' => $cout_fixe],
    'PESSIMISTE' => ['probabilite' => 20, 'ca' => $ca * 0.8, 'cv' => $cout_variable * 0.9, 'cf' => $cout_fixe * 0.95]
];

$esperance_resultat = 0;
foreach($scenarios as $nom => $s) {
    $resultat_scenario = $s['ca'] - $s['cv'] - $s['cf'];
    $esperance_resultat += $resultat_scenario * $s['probabilite'] / 100;
}

// Sauvegarde des résultats
$pdo->prepare("INSERT INTO RESULTATS_ANALYSE (exercice, seuil_rentabilite, marge_securite, indice_securite, levier_operationnel, ebe, caf, date_calcul) VALUES (2026, ?, ?, ?, ?, ?, ?, CURDATE()) ON DUPLICATE KEY UPDATE seuil_rentabilite = ?")->execute([$seuil_rentabilite, $marge_securite, $indice_securite, $levier_operationnel, $ebe, $caf, $seuil_rentabilite]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Analyse financière avancée - Société Générale 2026</h5>
                <small>Seuil de rentabilité, EBE, CAF, Levier opérationnel</small>
            </div>
            <div class="card-body">
                
                <!-- Données de base -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="card bg-success text-white text-center"><div class="card-body"><h4><?= number_format($ca, 0, ',', ' ') ?> F</h4><small>Chiffre d'affaires</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-danger text-white text-center"><div class="card-body"><h4><?= number_format($cout_fixe, 0, ',', ' ') ?> F</h4><small>Coûts fixes</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-warning text-dark text-center"><div class="card-body"><h4><?= number_format($cout_variable, 0, ',', ' ') ?> F</h4><small>Coûts variables</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-info text-white text-center"><div class="card-body"><h4><?= number_format($taux_marge, 2) ?>%</h4><small>Taux marge/cv</small></div></div></div>
                </div>

                <!-- Onglets -->
                <ul class="nav nav-tabs" id="analyseTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#seuil">🎯 Seuil de rentabilité</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ebe">📊 EBE & CAF</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#levier">⚙️ Levier opérationnel</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#aleatoire">🎲 Avenir aléatoire</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Onglet Seuil de rentabilité -->
                    <div class="tab-pane fade show active" id="seuil">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>📖 Définition :</strong> Le seuil de rentabilité est le chiffre d'affaires pour lequel l'entreprise ne réalise ni bénéfice ni perte.
                                </div>
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h4><?= number_format($seuil_rentabilite, 0, ',', ' ') ?> F</h4>
                                                <small>Seuil de rentabilité (CA critique)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h4><?= number_format($marge_securite, 0, ',', ' ') ?> F</h4>
                                                <small>Marge de sécurité</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h4><?= number_format($indice_securite, 2) ?>%</h4>
                                                <small>Indice de sécurité</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📈 Interprétation :</strong><br>
                                    <?php if($indice_securite > 30): ?>
                                        ✅ Situation très confortable - L'entreprise peut supporter une baisse d'activité de <?= number_format($indice_securite, 1) ?>%
                                    <?php elseif($indice_securite > 10): ?>
                                        ✅ Situation acceptable - Marge de sécurité de <?= number_format($indice_securite, 1) ?>%
                                    <?php else: ?>
                                        ⚠️ Situation fragile - Faible marge de sécurité (<?= number_format($indice_securite, 1) ?>%)
                                    <?php endif; ?>
                                </div>
                                <canvas id="seuilChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet EBE et CAF -->
                    <div class="tab-pane fade" id="ebe">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>📖 Définitions :</strong><br>
                                    - <strong>EBE (Excédent Brut d'Exploitation)</strong> = CA - Charges variables - Charges fixes d'exploitation<br>
                                    - <strong>CAF (Capacité d'Autofinancement)</strong> = EBE - Charges financières
                                </div>
                                <div class="row text-center">
                                    <div class="col-md-6">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h4><?= number_format($ebe, 0, ',', ' ') ?> F</h4>
                                                <small>Excédent Brut d'Exploitation (EBE)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h4><?= number_format($caf, 0, ',', ' ') ?> F</h4>
                                                <small>Capacité d'Autofinancement (CAF)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6>📊 Calcul détaillé :</h6>
                                    <table class="table table-bordered">
                                        <tr><td>Chiffre d'affaires<\/td><td class="text-end"><?= number_format($ca, 0, ',', ' ') ?> F</td></tr>
                                        <tr><td>- Charges variables<\/td><td class="text-end">- <?= number_format($cout_variable, 0, ',', ' ') ?> F<\/td></tr>
                                        <tr><td>- Charges fixes d'exploitation<\/td><td class="text-end">- <?= number_format($salaires + $services, 0, ',', ' ') ?> F<\/td></tr>
                                        <tr class="table-primary fw-bold"><td>= EBE<\/td><td class="text-end"><?= number_format($ebe, 0, ',', ' ') ?> F<\/td></tr>
                                        <tr><td>- Charges financières<\/td><td class="text-end">- <?= number_format($charges_fin, 0, ',', ' ') ?> F<\/td></tr>
                                        <tr class="table-success fw-bold"><td>= CAF<\/td><td class="text-end"><?= number_format($caf, 0, ',', ' ') ?> F<\/td></tr>
                                    </table>
                                </div>
                                <div class="alert alert-secondary mt-3">
                                    <strong>💡 Interprétation CAF :</strong><br>
                                    <?php if($caf > 0): ?>
                                        ✅ L'entreprise génère une capacité d'autofinancement positive de <?= number_format($caf, 0, ',', ' ') ?> F. Elle peut financer ses investissements.
                                    <?php else: ?>
                                        ⚠️ La CAF est négative. L'entreprise a besoin de financement externe.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Levier opérationnel -->
                    <div class="tab-pane fade" id="levier">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>📖 Définition :</strong> Le levier opérationnel mesure la sensibilité du résultat aux variations du chiffre d'affaires.
                                </div>
                                <div class="text-center">
                                    <div class="card bg-warning text-dark">
                                        <div class="card-body">
                                            <h2><?= number_format($levier_operationnel, 2) ?></h2>
                                            <small>Levier opérationnel</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📈 Interprétation :</strong><br>
                                    <?php if($levier_operationnel > 3): ?>
                                        ✅ Levier élevé - Une variation de 1% du CA entraîne une variation de <?= number_format($levier_operationnel, 2) ?>% du résultat.
                                    <?php elseif($levier_operationnel > 1.5): ?>
                                        ➖ Levier modéré - Sensibilité moyenne aux variations d'activité.
                                    <?php else: ?>
                                        ⚠️ Levier faible - Peu de sensibilité aux variations du CA.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Avenir aléatoire -->
                    <div class="tab-pane fade" id="aleatoire">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <strong>📖 Principe :</strong> Analyse en avenir aléatoire (probabiliste) - 3 scénarios avec probabilités.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr><th>Scénario</th><th>Probabilité</th><th>CA (F)</th><th>Coûts variables</th><th>Coûts fixes</th><th>Résultat (F)</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($scenarios as $nom => $s): 
                                                $res = $s['ca'] - $s['cv'] - $s['cf'];
                                            ?>
                                            <tr class="<?= $nom == 'OPTIMISTE' ? 'table-success' : ($nom == 'PESSIMISTE' ? 'table-danger' : '') ?>">
                                                <td><?= $nom ?></td>
                                                <td class="text-center"><?= $s['probabilite'] ?>%</td>
                                                <td class="text-end"><?= number_format($s['ca'], 0, ',', ' ') ?> F</td>
                                                <td class="text-end"><?= number_format($s['cv'], 0, ',', ' ') ?> F</td>
                                                <td class="text-end"><?= number_format($s['cf'], 0, ',', ' ') ?> F</td>
                                                <td class="text-end fw-bold"><?= number_format($res, 0, ',', ' ') ?> F</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-primary fw-bold">
                                                <td colspan="5">ESPÉRANCE MATHÉMATIQUE DU RÉSULTAT</td>
                                                <td class="text-end"><?= number_format($esperance_resultat, 0, ',', ' ') ?> F</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📊 Interprétation :</strong><br>
                                    Le résultat attendu en avenir aléatoire est de <strong><?= number_format($esperance_resultat, 0, ',', ' ') ?> F</strong>.<br>
                                    <?php if($esperance_resultat > 0): ?>
                                        ✅ Le projet est rentable en moyenne.
                                    <?php else: ?>
                                        ⚠️ Le projet n'est pas rentable en moyenne.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('seuilChart'), {
    type: 'line',
    data: {
        labels: ['0', '<?= number_format($seuil_rentabilite/2, 0, ',', '') ?>', '<?= number_format($seuil_rentabilite, 0, ',', '') ?>', '<?= number_format($ca, 0, ',', '') ?>'],
        datasets: [
            {label: 'Chiffre d\'affaires', data: [0, <?= $seuil_rentabilite/2 ?>, <?= $seuil_rentabilite ?>, <?= $ca ?>], borderColor: '#28a745', fill: false},
            {label: 'Coûts totaux', data: [<?= $cout_fixe ?>, <?= $cout_fixe + ($seuil_rentabilite/2)*($cout_variable/$ca) ?>, <?= $cout_fixe + $cout_variable ?>, <?= $cout_fixe + $cout_variable ?>], borderColor: '#dc3545', fill: false}
        ]
    },
    options: { responsive: true, maintainAspectRatio: true }
});
</script>

<?php include 'inc_footer.php'; ?>
