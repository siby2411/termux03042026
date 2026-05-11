<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Provisions et dépréciations";
$page_icon = "shield";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

// Constitution d'une provision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'constituer_provision') {
    $date = $_POST['date_constitution'];
    $libelle = $_POST['libelle'];
    $type = $_POST['type_provision'];
    $sous_type = $_POST['sous_type'];
    $compte_dotation = $_POST['compte_dotation'];
    $compte_provision = $_POST['compte_provision'];
    $montant = $_POST['montant'];
    $justificatif = $_POST['justificatif'];
    
    $stmt = $pdo->prepare("INSERT INTO PROVISIONS_DEPRECIATIONS (date_constitution, libelle, type_provision, sous_type, compte_dotation, compte_provision, montant_initial, montant_actuel, justificatif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $libelle, $type, $sous_type, $compte_dotation, $compte_provision, $montant, $montant, $justificatif]);
    
    // Écriture comptable
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'PROVISION')";
    $stmt2 = $pdo->prepare($sql);
    $stmt2->execute([$date, "Dotation provision - $libelle", $compte_dotation, $compte_provision, $montant, "PROV-" . date('Ymd')]);
    
    $message = "✅ Provision constituée et écriture comptable générée.";
}

// Reprise d'une provision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'reprendre_provision') {
    $id = $_POST['provision_id'];
    $prov = $pdo->prepare("SELECT * FROM PROVISIONS_DEPRECIATIONS WHERE id = ?");
    $prov->execute([$id]);
    $p = $prov->fetch();
    if ($p) {
        $update = $pdo->prepare("UPDATE PROVISIONS_DEPRECIATIONS SET statut = 'REPRISE', date_reprise = CURDATE(), montant_reprise = montant_actuel WHERE id = ?");
        $update->execute([$id]);
        // Écriture de reprise
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'REPRISE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([date('Y-m-d'), "Reprise provision - {$p['libelle']}", $p['compte_provision'], 781, $p['montant_actuel'], "REP-" . date('Ymd')]);
        $message = "✅ Provision reprise et écriture comptable générée.";
    }
}

$provisions = $pdo->query("SELECT * FROM PROVISIONS_DEPRECIATIONS WHERE statut = 'ACTIVE' ORDER BY date_constitution DESC")->fetchAll();
$total_provisions = array_sum(array_column($provisions, 'montant_actuel'));
?>
<!-- HTML d'affichage -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-danger text-white">Provisions et dépréciations</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<div class="row mb-3"><div class="col-md-4">Total provisions actives : <strong><?= number_format($total_provisions,0,',',' ') ?> F</strong></div>
<div class="col-md-8 text-end"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProvisionModal">Nouvelle provision</button></div></div>
<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th>Type</th><th>Compte D</th><th>Compte C</th><th>Montant</th><th>Actions</th></tr></thead>
<tbody><?php foreach($provisions as $p): ?><tr><td><?= $p['date_constitution'] ?></td><td><?= $p['libelle'] ?></td><td><?= $p['type_provision'] ?></td><td><?= $p['compte_dotation'] ?></td><td><?= $p['compte_provision'] ?></td><td class="text-end"><?= number_format($p['montant_actuel'],0,',',' ') ?> F</td>
<td><form method="POST" style="display:inline"><input type="hidden" name="action" value="reprendre_provision"><input type="hidden" name="provision_id" value="<?= $p['id'] ?>"><button class="btn btn-sm btn-warning" onclick="return confirm('Reprendre cette provision ?')">Reprendre</button></form></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<!-- Modal nouvelle provision -->
<div class="modal fade" id="newProvisionModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5>Constituer une provision</h5></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="constituer_provision"><div class="mb-2"><label>Date</label><input type="date" name="date_constitution" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
<div class="mb-2"><label>Type</label><select name="type_provision" class="form-select"><option>RISQUES</option><option>CHARGES</option><option>DEPRECIATION_ACTIF</option></select></div>
<div class="mb-2"><label>Sous-type</label><input type="text" name="sous_type" class="form-control"></div>
<div class="mb-2"><label>Compte de dotation (68X)</label><input type="number" name="compte_dotation" class="form-control" value="681" required></div>
<div class="mb-2"><label>Compte de provision (15/49)</label><input type="number" name="compte_provision" class="form-control" value="161" required></div>
<div class="mb-2"><label>Montant (F)</label><input type="number" name="montant" class="form-control" required></div>
<div class="mb-2"><label>Justificatif</label><textarea name="justificatif" class="form-control"></textarea></div></div>
<div class="modal-footer"><button type="submit" class="btn btn-danger">Constituer</button></div></form></div></div></div>
<?php include 'inc_footer.php'; ?>
