<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Variation des Capitaux Propres";
$page_icon = "arrow-repeat";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Calcul des capitaux propres par exercice
$capitaux_propres = [];
for ($annee = 2024; $annee <= date('Y'); $annee++) {
    $capital = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 101 AND YEAR(date_ecriture) = ?");
    $capital->execute([$annee]);
    $cap = $capital->fetchColumn();
    
    $resultat = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799 AND YEAR(date_ecriture) = ?");
    $resultat->execute([$annee]);
    $prod = $resultat->fetchColumn();
    
    $charges_stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699 AND YEAR(date_ecriture) = ?");
    $charges_stmt->execute([$annee]);
    $charg = $charges_stmt->fetchColumn();
    
    $reserves = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 118 AND 119 AND YEAR(date_ecriture) = ?");
    $reserves->execute([$annee]);
    $res = $reserves->fetchColumn();
    
    $capitaux_propres[$annee] = [
        'capital' => $cap,
        'resultat' => $prod - $charg,
        'reserves' => $res,
        'total' => $cap + ($prod - $charg) + $res
    ];
}

// Récupération du dernier exercice
$dernier_exercice = date('Y');
$total_capitaux = $capitaux_propres[$dernier_exercice]['total'];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-repeat"></i> Tableau de Variation des Capitaux Propres</h5>
                <small>Suivi de l'évolution du patrimoine net - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <!-- Graphique d'évolution -->
                <div class="row">
                    <div class="col-md-12">
                        <canvas id="capitauxChart" height="100"></canvas>
                    </div>
                </div>
                
                <!-- Tableau détaillé -->
                <h6 class="mt-4"><i class="bi bi-table"></i> Détail par exercice</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Exercice</th>
                                <th>Capital social</th>
                                <th>Résultat de l'exercice</th>
                                <th>Réserves</th>
                                <th>Capitaux Propres</th>
                                <th>Variation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $precedent = 0;
                            foreach($capitaux_propres as $annee => $data): 
                                $variation = $data['total'] - $precedent;
                            ?>
                            <tr class="text-center">
                                <td class="fw-bold"><?= $annee ?></td>
                                <td class="text-end"><?= number_format($data['capital'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $data['resultat'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($data['resultat']), 0, ',', ' ') ?> F
                                </td>
                                <td class="text-end"><?= number_format($data['reserves'], 0, ',', ' ') ?> F</td>
                                <td class="text-end fw-bold text-primary"><?= number_format($data['total'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $variation >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= ($variation >= 0 ? '+' : '-') . number_format(abs($variation), 0, ',', ' ') ?> F
                                </td>
                            </tr>
                            <?php 
                            $precedent = $data['total'];
                            endforeach; 
                            ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td class="text-end" colspan="4">TOTAL CAPITAUX PROPRES :</td>
                                <td class="text-end text-primary"><?= number_format($total_capitaux, 0, ',', ' ') ?> F</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Indicateurs de structure -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-calculator fs-2 text-primary"></i>
                                <h6>Ratio d'endettement</h6>
                                <h4>0%</h4>
                                <small>Dettes / Capitaux propres</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-percent fs-2 text-success"></i>
                                <h6>Autonomie financière</h6>
                                <h4><?= number_format(($total_capitaux / ($total_capitaux + 1)) * 100, 1) ?>%</h4>
                                <small>Capitaux propres / Total ressources</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-trophy fs-2 text-warning"></i>
                                <h6>Performance annuelle</h6>
                                <h4 class="text-success">+<?= number_format($precedent - ($capitaux_propres[2024]['total'] ?? 0), 0, ',', ' ') ?> F</h4>
                                <small>Évolution sur période</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 INTERPRÉTATION :</strong><br>
                    Les capitaux propres représentent la richesse nette de l'entreprise. Leur augmentation signifie une amélioration de la situation financière.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('capitauxChart'), {
    type: 'line',
    data: {
        labels: ['<?= implode("', '", array_keys($capitaux_propres)) ?>'],
        datasets: [{
            label: 'Capitaux Propres (FCFA)',
            data: [<?= implode(", ", array_column($capitaux_propres, 'total')) ?>],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat().format(context.raw) + ' FCFA';
                    }
                }
            }
        }
    }
});
</script>

<?php include 'inc_footer.php'; ?>
