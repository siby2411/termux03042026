<?php
require_once "../includes/db.php";
require_once "layout.php";
// KPI 1 : CA
$ca = $pdo->query("
    SELECT SUM(montant) AS total 
    FROM ECRITURES_COMPTABLES 
    WHERE compte_debite_id = 2100 OR compte_debite_id BETWEEN 700 AND 709
")->fetchColumn();

// KPI 2 : Cash (Banque + Caisse)
$cash = $pdo->query("
    SELECT 
        (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id=2200)
      - (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id=2200)
      +
        (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id=2210)
      - (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id=2210)
")->fetchColumn();

// KPI 3 : Résultat Net
$res = $pdo->query("
    SELECT 
    (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799)
    -
    (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699)
")->fetchColumn();

// KPI 4 : EBE
$ebe = $pdo->query("
    SELECT 
    (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 701 AND 709)
    -
    (SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (601,602,604,610,620))
")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light p-4">

<div class="container">

<h2 class="mb-4 text-center">Dashboard Comptable</h2>

<div class="row g-3">

    <div class="col-md-3">
        <div class="card bg-primary text-white text-center p-3">
            <h5>Chiffre d'affaires</h5>
            <p class="fs-3"><?= number_format($ca,0,',',' ') ?> F</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white text-center p-3">
            <h5>Cash</h5>
            <p class="fs-3"><?= number_format($cash,0,',',' ') ?> F</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-dark text-center p-3">
            <h5>Résultat Net</h5>
            <p class="fs-3"><?= number_format($res,0,',',' ') ?> F</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-dark text-center p-3">
            <h5>EBE</h5>
            <p class="fs-3"><?= number_format($ebe,0,',',' ') ?> F</p>
        </div>
    </div>

</div>

<hr>

<h4 class="mt-4">Graphique de performance mensuelle</h4>
<canvas id="kpiChart"></canvas>

<script>
let ctx = document.getElementById('kpiChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ["CA","Cash","Résultat", "EBE"],
        datasets: [{
            label: "Valeurs (F CFA)",
            data: [<?= $ca ?>, <?= $cash ?>, <?= $res ?>, <?= $ebe ?>]
        }]
    }
});
</script>

<hr>

<h4>Modules</h4>

<div class="list-group">
    <a href="bilan.php" class="list-group-item list-group-item-action">Bilan</a>
    <a href="sig.php" class="list-group-item list-group-item-action">SIG</a>
    <a href="flux.php" class="list-group-item list-group-item-action">Flux de trésorerie</a>
    <a href="ecriture.php" class="list-group-item list-group-item-action">Saisie des écritures</a>
    <a href="ajout_compte.php" class="list-group-item list-group-item-action">Ajouter un compte comptable</a>
</div>

</div>
</body>
</html>

