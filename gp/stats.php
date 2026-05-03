<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container-fluid">
    <h2><i class="fas fa-chart-line"></i> Statistiques et états financiers</h2>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Colis Paris ↔ Dakar</div>
                <div class="card-body">
                    <?php
                    $parisDakar = $pdo->query("SELECT COUNT(*) FROM colis WHERE lieu_depart = 'Paris' AND lieu_arrivee = 'Dakar'")->fetchColumn();
                    $dakarParis = $pdo->query("SELECT COUNT(*) FROM colis WHERE lieu_depart = 'Dakar' AND lieu_arrivee = 'Paris'")->fetchColumn();
                    ?>
                    <canvas id="traficChart" width="400" height="200"></canvas>
                    <script>
                        new Chart(document.getElementById("traficChart"), { type: 'bar', data: { labels: ['Paris → Dakar', 'Dakar → Paris'], datasets: [{ label: 'Nombre de colis', data: [<?= $parisDakar ?>, <?= $dakarParis ?>], backgroundColor: ['#ff6384', '#36a2eb'] }] } });
                    </script>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">État financier</div>
                <div class="card-body">
                    <?php
                    $total_encaisse = $pdo->query("SELECT SUM(montant_encaisse) FROM colis")->fetchColumn();
                    $total_charges = $pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn();
                    $benefice = $total_encaisse - $total_charges;
                    ?>
                    <canvas id="financeChart" width="400" height="200"></canvas>
                    <script>
                        new Chart(document.getElementById("financeChart"), { type: 'pie', data: { labels: ['Encaissements', 'Charges', 'Bénéfice'], datasets: [{ data: [<?= $total_encaisse ?>, <?= $total_charges ?>, <?= $benefice ?>], backgroundColor: ['#28a745', '#dc3545', '#ffc107'] }] } });
                    </script>
                    <ul class="list-group mt-3">
                        <li class="list-group-item">Total encaissé : <strong><?= number_format($total_encaisse,2) ?> €</strong></li>
                        <li class="list-group-item">Total charges : <strong><?= number_format($total_charges,2) ?> €</strong></li>
                        <li class="list-group-item">Bénéfice net : <strong class="<?= $benefice >=0 ? 'text-success' : 'text-danger' ?>"><?= number_format($benefice,2) ?> €</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">Évolution mensuelle</div>
                <div class="card-body">
                    <canvas id="evolutionChart" width="800" height="300"></canvas>
                    <?php
                    $evolution = $pdo->query("SELECT DATE_FORMAT(date_charge, '%Y-%m') as mois, SUM(montant) as total_charges FROM charges GROUP BY mois ORDER BY mois")->fetchAll();
                    $encaissements = $pdo->query("SELECT '2026-04' as mois, SUM(montant_encaisse) as total_encaissements FROM colis")->fetchAll();
                    $labels = []; $chargesData = []; $encaissementsData = [];
                    foreach ($evolution as $e) { $labels[] = $e['mois']; $chargesData[] = $e['total_charges']; }
                    foreach ($encaissements as $enc) { $encaissementsData[] = $enc['total_encaissements']; }
                    ?>
                    <script>
                        new Chart(document.getElementById("evolutionChart"), { type: 'line', data: { labels: <?= json_encode($labels) ?>, datasets: [{ label: 'Charges', data: <?= json_encode($chargesData) ?>, borderColor: 'red' }, { label: 'Encaissements', data: <?= json_encode($encaissementsData) ?>, borderColor: 'green' }] } });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
