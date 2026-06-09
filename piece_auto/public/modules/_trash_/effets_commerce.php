<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Effets de commerce";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'creer') {
        $numero = "EFF-" . date('Ymd') . "-" . rand(100,999);
        $date = $_POST['date'];
        $echeance = $_POST['echeance'];
        $type = $_POST['type'];
        $nature = $_POST['nature'];
        $tiers_id = $_POST['tiers_id'];
        $montant = $_POST['montant'];
        $stmt = $pdo->prepare("INSERT INTO EFFETS_COMMERCE (numero_effet, date_creation, date_echeance, type_effet, nature, tiers_id, montant) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$numero, $date, $echeance, $type, $nature, $tiers_id, $montant]);
        if ($nature == 'CLIENT') {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 4111, 701, ?, ?, 'EFFET')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date, "Vente avec effet $numero", $montant, $numero]);
        } else {
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 601, 4011, ?, ?, 'EFFET')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date, "Achat avec effet $numero", $montant, $numero]);
        }
        $message = "✅ Effet créé et écriture générée.";
    }
    if ($_POST['action'] === 'escompter') {
        $id = $_POST['id'];
        $taux = (float)$_POST['taux'];
        $date_escompte = $_POST['date_escompte'];
        $effet = $pdo->prepare("SELECT * FROM EFFETS_COMMERCE WHERE id = ?");
        $effet->execute([$id]);
        $e = $effet->fetch();
        $jours = (strtotime($e['date_echeance']) - strtotime($date_escompte)) / 86400;
        $interets = $e['montant'] * $taux / 100 * $jours / 360;
        $commission = $e['montant'] * 0.005;
        $agios = $interets + $commission;
        $net = $e['montant'] - $agios;
        $update = $pdo->prepare("UPDATE EFFETS_COMMERCE SET statut = 'ESCOMPTE', taux_escompte = ?, frais_escompte = ?, agios = ?, commission = ?, montant_net = ? WHERE id = ?");
        $update->execute([$taux, $interets, $agios, $commission, $net, $id]);
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
                (?, ?, 521, 4111, ?, ?, 'ESCOMPTE'),
                (?, ?, 631, 4111, ?, ?, 'ESCOMPTE'),
                (?, ?, 637, 4111, ?, ?, 'ESCOMPTE')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date_escompte, "Escompte effet {$e['numero_effet']}", $net, $e['numero_effet'], $date_escompte, "Agios {$e['numero_effet']}", $interets, $e['numero_effet'], $date_escompte, "Commission {$e['numero_effet']}", $commission, $e['numero_effet']]);
        $message = "✅ Effet escompté - Net : " . number_format($net,0,',',' ') . " F";
    }
}
$effets = $pdo->query("SELECT e.*, t.raison_sociale FROM EFFETS_COMMERCE e JOIN TIERS t ON e.tiers_id = t.id ORDER BY e.date_echeance")->fetchAll();
$tiers = $pdo->query("SELECT * FROM TIERS WHERE type IN ('CLIENT','FOURNISSEUR')")->fetchAll();
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Effets de commerce</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<form method="POST" class="row g-3"><input type="hidden" name="action" value="creer">
<div class="col-md-2"><label>Type</label><select name="type" class="form-select"><option>LETTRE_CHANGE</option><option>BILLET_A_ORDRE</option></select></div>
<div class="col-md-2"><label>Nature</label><select name="nature" class="form-select"><option value="CLIENT">Client</option><option value="FOURNISSEUR">Fournisseur</option></select></div>
<div class="col-md-3"><label>Tiers</label><select name="tiers_id" class="form-select"><?php foreach($tiers as $t): ?><option value="<?= $t['id'] ?>"><?= $t['raison_sociale'] ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label>Date création</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="col-md-2"><label>Échéance</label><input type="date" name="echeance" class="form-control"></div>
<div class="col-md-1"><label>Montant</label><input type="number" name="montant" class="form-control" required></div>
<div class="col-12"><button type="submit" class="btn-omega">Créer effet</button></div></form>
<hr>
<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"></table><th>N°</th><th>Date</th><th>Échéance</th><th>Tiers</th><th>Montant</th><th>Statut</th><th>Action</th></tr></thead>
<tbody><?php foreach($effets as $e): ?><tr><td><?= $e['numero_effet'] ?></td><td><?= $e['date_creation'] ?></td><td><?= $e['date_echeance'] ?></td><td><?= $e['raison_sociale'] ?></td><td class="text-end"><?= number_format($e['montant'],0,',',' ') ?> F</td>
<td><?= $e['statut'] ?></td><td><?php if($e['statut'] == 'EN_PORTEFEUILLE'): ?><form method="POST"><input type="hidden" name="action" value="escompter"><input type="hidden" name="id" value="<?= $e['id'] ?>"><input type="date" name="date_escompte" value="<?= date('Y-m-d') ?>"><input type="number" name="taux" placeholder="Taux %" step="0.1" required><button class="btn btn-sm btn-warning">Escompter</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
