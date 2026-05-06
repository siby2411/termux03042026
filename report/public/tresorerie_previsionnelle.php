<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$page_title = "Trésorerie Prévisionnelle";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$encaissements = [];
$decaissements = [];

for($i = 1; $i <= 12; $i++) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND compte_credite_id BETWEEN 70 AND 79");
    $stmt->execute([$i]);
    $encaissements[] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE MONTH(date_ecriture) = ? AND compte_debite_id BETWEEN 60 AND 69");
    $stmt->execute([$i]);
    $decaissements[] = $stmt->fetchColumn();
}
?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-cash-stack"></i> Trésorerie Prévisionnelle <?= date('Y') ?></h5>
    </div>
    <div class="card-body">
        <canvas id="treasuryChart" height="100"></canvas>
        <div class="alert alert-info mt-3">
            <i class="bi bi-graph-up"></i> Solde projeté fin d'exercice : 
            <strong class="text-primary"><?= number_format(array_sum($encaissements) - array_sum($decaissements), 0, ',', ' ') ?> FCFA</strong>
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
            {label: 'Encaissements (Ventes)', data: <?= json_encode($encaissements) ?>, borderColor: '#28a745', fill: false},
            {label: 'Décaissements (Charges)', data: <?= json_encode($decaissements) ?>, borderColor: '#dc3545', fill: false}
        ]
    }
});
</script>
<?php include 'inc_footer.php'; ?>
