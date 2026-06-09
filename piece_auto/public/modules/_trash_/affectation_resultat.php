<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Affectation du résultat";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';
$exercice = date('Y');
$produits = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$charges = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$resultat_net = $produits - $charges;
$est_benefice = $resultat_net > 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = 0;
    $reserve_legale = (float)$_POST['reserve_legale'];
    $dividendes = (float)$_POST['dividendes'];
    $autres_reserves = (float)$_POST['autres_reserves'];
    $report = (float)$_POST['report'];
    $total = $reserve_legale + $dividendes + $autres_reserves + $report;
    if (abs($total - $resultat_net) > 1) {
        $message = "⚠️ Le total des affectations ($total) ne correspond pas au résultat net ($resultat_net).";
    } else {
        $stmt = $pdo->prepare("INSERT INTO AFFECTATIONS_RESULTAT (exercice, montant_total, date_proposition, statut) VALUES (?, ?, CURDATE(), 'PROPOSEE')");
        $stmt->execute([$exercice, $resultat_net]);
        $affect_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO LIGNES_AFFECTATION (affectation_id, compte_id, libelle, montant) VALUES (?, ?, ?, ?)");
        if ($reserve_legale > 0) $stmt2->execute([$affect_id, 114, 'Réserve légale', $reserve_legale]);
        if ($dividendes > 0) $stmt2->execute([$affect_id, 457, 'Dividendes', $dividendes]);
        if ($autres_reserves > 0) $stmt2->execute([$affect_id, 118, 'Autres réserves', $autres_reserves]);
        if ($report > 0 && $est_benefice) $stmt2->execute([$affect_id, 112, 'Report à nouveau créditeur', $report]);
        elseif ($report > 0 && !$est_benefice) $stmt2->execute([$affect_id, 113, 'Report à nouveau débiteur', $report]);
        $message = "✅ Proposition d'affectation enregistrée (statut PROPOSEE).";
    }
}
$historique = $pdo->query("SELECT a.*, COUNT(l.id) as nb_lignes FROM AFFECTATIONS_RESULTAT a LEFT JOIN LIGNES_AFFECTATION l ON a.id = l.affectation_id GROUP BY a.id ORDER BY a.date_proposition DESC")->fetchAll();
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white"><h5>Affectation du résultat – Exercice <?= $exercice ?></h5><small>Résultat net : <strong class="<?= $est_benefice ? 'text-success' : 'text-danger' ?>"><?= number_format(abs($resultat_net),0,',',' ') ?> F <?= $est_benefice ? '(Bénéfice)' : '(Perte)' ?></strong></small></div><div class="card-body">
<?php if($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
<form method="POST" class="row g-3"><div class="col-md-3"><label>Réserve légale (5% mini)</label><input type="number" name="reserve_legale" class="form-control" step="1000" value="<?= $est_benefice ? min($resultat_net*0.05, $resultat_net) : 0 ?>"></div>
<div class="col-md-3"><label>Dividendes</label><input type="number" name="dividendes" class="form-control" step="1000"></div>
<div class="col-md-3"><label>Autres réserves</label><input type="number" name="autres_reserves" class="form-control" step="1000"></div>
<div class="col-md-3"><label>Report à nouveau</label><input type="number" name="report" class="form-control" step="1000"></div>
<div class="col-12"><button type="submit" class="btn-omega">Proposer l'affectation</button></div></form>
<hr><h6>Historique des propositions</h6><div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Date</th><th>Exercice</th><th>Montant</th><th>Statut</th><th>Nb lignes</th><th>Actions</th></tr></thead><tbody><?php foreach($historique as $h): ?><tr><td><?= $h['date_proposition'] ?></td><td><?= $h['exercice'] ?></td><td class="text-end"><?= number_format($h['montant_total'],0,',',' ') ?> F</td><td><span class="badge <?= $h['statut']=='APPROUVEE' ? 'bg-success' : ($h['statut']=='REJETEE' ? 'bg-danger' : 'bg-warning') ?>"><?= $h['statut'] ?></span></td><td class="text-center"><?= $h['nb_lignes'] ?></td><td><button class="btn btn-sm btn-info" onclick="alert('Détail non implémenté')">Détail</button></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
