<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Trésorerie Prévisionnelle";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$encaissements = [];
$decaissements = [];

for($i = 1; $i <= 12; $i++) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
    $stmt->execute([$i, date('Y')]);
    $encaissements[] = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 600 AND 699");
    $stmt->execute([$i, date('Y')]);
    $decaissements[] = $stmt->fetchColumn() ?: 0;
}

$total_encaissements = array_sum($encaissements);
$total_decaissements = array_sum($decaissements);
$solde_previsionnel = $total_encaissements - $total_decaissements;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cash-stack"></i> Trésorerie Prévisionnelle <?= date('Y') ?></h5>
                <small>Flux de trésorerie prévisionnels basés sur les écritures réelles</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_encaissements, 0, ',', ' ') ?> F</h4>
                                <small>Encaissements prévus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_decaissements, 0, ',', ' ') ?> F</h4>
                                <small>Décaissements prévus</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($solde_previsionnel, 0, ',', ' ') ?> F</h4>
                                <small>Solde prévisionnel</small>
                            </div>
                        </div>
                    </div>
                </div>

                <canvas id="treasuryChart" height="100"></canvas>
                
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Mois</th><th class="text-end">Encaissements (F)</th><th class="text-end">Décaissements (F)</th><th class="text-end">Solde mensuel (F)</th><th>Analyse</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cumul = 0;
                            for($i = 0; $i < 12; $i++): 
                                $solde_mensuel = ($encaissements[$i] ?? 0) - ($decaissements[$i] ?? 0);
                                $cumul += $solde_mensuel;
                            ?>
                            <tr>
                                <td><b><?= $mois[$i] ?></b></td>
                                <td class="text-end text-success"><?= number_format($encaissements[$i] ?? 0, 0, ',', ' ') ?> F</td>
                                <td class="text-end text-danger"><?= number_format($decaissements[$i] ?? 0, 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $solde_mensuel >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($solde_mensuel, 0, ',', ' ') ?> F
                                </td>
                                <td>
                                    <?php if($encaissements[$i] > $decaissements[$i]): ?>
                                        ✅ Trésorerie excédentaire
                                    <?php elseif($encaissements[$i] < $decaissements[$i]): ?>
                                        ⚠️ Besoin de trésorerie
                                    <?php else: ?>
                                        ➖ Équilibre
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="table-secondary fw-bold">
                                <td>TOTAL / CUMULé</td>
                                <td class="text-end"><?= number_format($total_encaissements, 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($total_decaissements, 0, ',', ' ') ?> F</td>
                                <td class="text-end text-primary"><?= number_format($cumul, 0, ',', ' ') ?> F</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('treasuryChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($mois) ?>,
        datasets: [
            {label: 'Encaissements', data: <?= json_encode($encaissements) ?>, borderColor: '#28a745', fill: false},
            {label: 'Décaissements', data: <?= json_encode($decaissements) ?>, borderColor: '#dc3545', fill: false}
        ]
    },
    options: { responsive: true, maintainAspectRatio: true }
});
</script>

<?php include 'inc_footer.php'; ?>
