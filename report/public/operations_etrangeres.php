<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Opérations en devises";
$page_icon = "currency-exchange";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

$devises = $pdo->query("SELECT * FROM DEVISES ORDER BY code")->fetchAll();
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'ajouter') {
        $type = $_POST['type'];
        $date = $_POST['date'];
        $ref = $_POST['ref'];
        $tiers_id = $_POST['tiers_id'];
        $montant = (float)$_POST['montant'];
        $devise = $_POST['devise'];
        $stmt = $pdo->prepare("SELECT taux_fcfa FROM DEVISES WHERE code = ? ORDER BY date_taux DESC LIMIT 1");
        $stmt->execute([$devise]);
        $taux = $stmt->fetchColumn();
        $montant_fcfa = $montant * $taux;
        if ($type == 'EXPORT') {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 411, 701, ?, ?, 'EXPORT'), (?, ?, 701, 4451, ?, ?, 'EXPORT')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date, "Facture export $ref", $montant_fcfa, $ref, $date, "TVA export $ref", $montant_fcfa*0.18, $ref]);
        } else {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 601, 401, ?, ?, 'IMPORT'), (?, ?, 4454, 401, ?, ?, 'IMPORT')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date, "Import $ref", $montant_fcfa, $ref, $date, "TVA import $ref", $montant_fcfa*0.18, $ref]);
        }
        $stmt3 = $pdo->prepare("INSERT INTO OPERATIONS_ETRANGERES (type_operation, date_operation, reference, tiers_id, montant_devise, code_devise, taux_originel) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt3->execute([$type, $date, $ref, $tiers_id, $montant, $devise, $taux]);
        $message = "✅ Opération enregistrée - Montant FCFA : " . number_format($montant_fcfa,0,',',' ') . " F";
    }
    if ($_POST['action'] === 'regler') {
        $id = $_POST['id'];
        $date_reg = $_POST['date_reg'];
        $taux_reg = (float)$_POST['taux_reg'];
        $op = $pdo->prepare("SELECT * FROM OPERATIONS_ETRANGERES WHERE id = ?");
        $op->execute([$id]);
        $o = $op->fetch();
        $montant_fcfa_orig = $o['montant_fcfa_originel'];
        $montant_reg = $o['montant_devise'] * $taux_reg;
        $ecart = $montant_reg - $montant_fcfa_orig;
        if ($o['type_operation'] == 'EXPORT') {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 411, ?, ?, 'REGLEMENT')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date_reg, "Règlement {$o['reference']}", $montant_reg, $o['reference']]);
            if ($ecart != 0) {
                if ($ecart > 0) $sql_ecart = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 476, 411, ?, ?, 'ECART')";
                else $sql_ecart = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 411, 676, ?, ?, 'ECART')";
                $stmt3 = $pdo->prepare($sql_ecart);
                $stmt3->execute([$date_reg, "Écart de change {$o['reference']}", abs($ecart), $o['reference']]);
            }
        } else {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 401, 521, ?, ?, 'REGLEMENT')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date_reg, "Paiement {$o['reference']}", $montant_reg, $o['reference']]);
            if ($ecart != 0) {
                if ($ecart > 0) $sql_ecart = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 401, 766, ?, ?, 'ECART')";
                else $sql_ecart = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 666, 401, ?, ?, 'ECART')";
                $stmt3 = $pdo->prepare($sql_ecart);
                $stmt3->execute([$date_reg, "Écart de change {$o['reference']}", abs($ecart), $o['reference']]);
            }
        }
        $update = $pdo->prepare("UPDATE OPERATIONS_ETRANGERES SET date_reglement = ?, taux_reglement = ?, montant_fcfa_reglement = ?, ecart_change = ? WHERE id = ?");
        $update->execute([$date_reg, $taux_reg, $montant_reg, $ecart, $id]);
        $message = "✅ Règlement enregistré - Écart : " . number_format(abs($ecart),0,',',' ') . " F (" . ($ecart>0?"gain":"perte") . ")";
    }
}
$ops = $pdo->query("SELECT o.*, t.raison_sociale FROM OPERATIONS_ETRANGERES o JOIN TIERS t ON o.tiers_id = t.id ORDER BY o.date_operation DESC")->fetchAll();
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Opérations en devises</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#new">➕ Nouvelle opération</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#list">📋 Liste</button></li></ul>
<div class="tab-content mt-3"><div class="tab-pane fade show active" id="new">
<form method="POST" class="row g-3"><input type="hidden" name="action" value="ajouter"><div class="col-md-2"><label>Type</label><select name="type" class="form-select"><option>EXPORT</option><option>IMPORT</option></select></div>
<div class="col-md-2"><label>Date</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="col-md-2"><label>Réf.</label><input type="text" name="ref" class="form-control" required></div>
<div class="col-md-3"><label>Tiers</label><select name="tiers_id" class="form-select"><?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= $c['raison_sociale'] ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label>Montant</label><input type="number" name="montant" class="form-control" step="0.01" required></div>
<div class="col-md-3"><label>Devise</label><select name="devise" class="form-select"><?php foreach($devises as $d): ?><option value="<?= $d['code'] ?>"><?= $d['code'] ?> (1 = <?= number_format($d['taux_fcfa'],2) ?> F)</option><?php endforeach; ?></select></div>
<div class="col-12"><button type="submit" class="btn-omega">Enregistrer</button></div></form></div>
<div class="tab-pane fade" id="list">
<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Date</th><th>Réf.</th><th>Type</th><th>Tiers</th><th>Montant devise</th><th>FCFA initial</th><th>Règlement</th><th>Écart</th><th>Action</th></tr></thead>
<tbody><?php foreach($ops as $o): ?><table><td><?= $o['date_operation'] ?></td><td><?= $o['reference'] ?></td><td><?= $o['type_operation'] ?></td><td><?= $o['raison_sociale'] ?></td><td class="text-end"><?= number_format($o['montant_devise'],2) ?> <?= $o['code_devise'] ?></td>
<td class="text-end"><?= number_format($o['montant_fcfa_originel'],0,',',' ') ?> F</td><td class="text-center"><?= $o['date_reglement'] ? date('d/m/Y',strtotime($o['date_reglement'])) : '-' ?></td>
<td class="<?= $o['ecart_change']>0?'text-success':($o['ecart_change']<0?'text-danger':'') ?>"><?= $o['ecart_change'] ? number_format($o['ecart_change'],0,',',' ').' F' : '-' ?></td>
<td><?php if(!$o['date_reglement']): ?><form method="POST"><input type="hidden" name="action" value="regler"><input type="hidden" name="id" value="<?= $o['id'] ?>"><input type="date" name="date_reg" value="<?= date('Y-m-d') ?>"><input type="number" name="taux_reg" step="0.0001" placeholder="Taux règlement" required><button class="btn btn-sm btn-success">Régler</button></form><?php else: ?>Soldé<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div></div></div>
<?php include 'inc_footer.php'; ?>
