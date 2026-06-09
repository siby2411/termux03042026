<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Rapprochement bancaire";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'encaissement') {
        $libelle = $_POST['libelle'];
        $compte_credit = (int)$_POST['compte_credit'];
        $montant = (float)$_POST['montant'];
        $ref = $_POST['reference'];
        $date = date('Y-m-d');
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, ?, ?, ?, 'TRESORERIE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date, $libelle, $compte_credit, $montant, $ref]);
        $message = "✅ Encaissement enregistré.";
    }
    if ($_POST['action'] === 'decaissement') {
        $libelle = $_POST['libelle'];
        $compte_debit = (int)$_POST['compte_debit'];
        $montant = (float)$_POST['montant'];
        $ref = $_POST['reference'];
        $date = date('Y-m-d');
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 521, ?, ?, 'TRESORERIE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date, $libelle, $compte_debit, $montant, $ref]);
        $message = "✅ Décaissement enregistré.";
    }
}
$operations = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 521 OR compte_credite_id = 521 ORDER BY date_ecriture DESC LIMIT 20")->fetchAll();
$solde = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END),0) FROM ECRITURES_COMPTABLES")->fetchColumn();
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Rapprochement bancaire</div>
<div class="card-body"><div class="alert alert-info">Solde actuel : <strong><?= number_format($solde,0,',',' ') ?> FCFA</strong></div>
<ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#enc">Encaissement</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#dec">Décaissement</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#releve">Relevé</button></li></ul>
<div class="tab-content mt-3"><div class="tab-pane fade show active" id="enc"><form method="POST" class="row g-2"><input type="hidden" name="action" value="encaissement"><div class="col-md-4"><input type="text" name="libelle" class="form-control" placeholder="Libellé" required></div><div class="col-md-3"><select name="compte_credit" class="form-select"><option value="701">701 - Ventes</option><option value="703">703 - Prestations</option></select></div><div class="col-md-2"><input type="number" name="montant" class="form-control" placeholder="Montant" required></div><div class="col-md-2"><input type="text" name="reference" class="form-control" placeholder="Réf."></div><div class="col-md-1"><button type="submit" class="btn-omega">OK</button></div></form></div>
<div class="tab-pane fade" id="dec"><form method="POST" class="row g-2"><input type="hidden" name="action" value="decaissement"><div class="col-md-4"><input type="text" name="libelle" class="form-control" placeholder="Libellé" required></div><div class="col-md-3"><select name="compte_debit" class="form-select"><option value="601">601 - Achats</option><option value="606">606 - Fournitures</option></select></div><div class="col-md-2"><input type="number" name="montant" class="form-control" placeholder="Montant" required></div><div class="col-md-2"><input type="text" name="reference" class="form-control" placeholder="Réf."></div><div class="col-md-1"><button type="submit" class="btn-omega">OK</button></div></form></div>
<div class="tab-pane fade" id="releve"><div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th>Référence</th><th>Encaissement</th><th>Décaissement</th></tr></thead>
<tbody><?php foreach($operations as $op): ?><tr><td><?= $op['date_ecriture'] ?></td><td><?= $op['libelle'] ?></td><td><?= $op['reference_piece'] ?></td>
<td class="text-success"><?= $op['compte_debite_id']==521 ? number_format($op['montant'],0,',',' ').' F' : '-' ?></td>
<td class="text-danger"><?= $op['compte_credite_id']==521 ? number_format($op['montant'],0,',',' ').' F' : '-' ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php if($message): ?><div class="alert alert-success mt-3"><?= $message ?></div><?php endif; ?>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
