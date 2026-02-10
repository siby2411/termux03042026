

<?php
$page_title = "Immobilisations";
require_once "../includes/auth_check.php";
require_once "../includes/db.php";
require_once "layout.php";

$immos = $pdo->query("
    SELECT date_operation, libelle_operation, montant, compte_debite_id,
    intitule_compte
    FROM ECRITURES_COMPTABLES 
    JOIN PLAN_COMPTABLE_UEMOA ON compte_debite_id = compte_id
    WHERE compte_debite_id BETWEEN 1200 AND 1299
")->fetchAll(PDO::FETCH_ASSOC);

$total_immo = 0;
foreach($immos as $i){ $total_immo += $i["montant"]; }
?>

<div class="card p-4 shadow-sm mb-4">
<h5>Liste des Immobilisations</h5>

<table class="table table-bordered">
<thead>
<tr><th>Date</th><th>Libellé</th><th>Compte</th><th>Montant</th></tr>
</thead>
<tbody>
<?php foreach($immos as $i): ?>
<tr>
<td><?= $i['date_operation'] ?></td>
<td><?= $i['libelle_operation'] ?></td>
<td><?= $i['compte_debite_id'] ?> - <?= $i['intitule_compte'] ?></td>
<td><?= number_format($i['montant'],2,',',' ') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="card p-4 shadow-sm">
<h5>Total des investissements</h5>
<canvas id="immoChart" height="120"></canvas>
</div>

<script>
new Chart(document.getElementById('immoChart'), {
    type:'doughnut',
    data:{
        labels:['Investissements'],
        datasets:[{data:[<?= $total_immo ?>], backgroundColor:['#10b981']}]
    }
});
</script>






