<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Effets de Commerce";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

// Création effet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'creer_effet') {
    $numero = "EFF-" . date('Ymd') . "-" . rand(100,999);
    $date_creation = $_POST['date_creation'];
    $date_echeance = $_POST['date_echeance'];
    $type_effet = $_POST['type_effet'];
    $nature = $_POST['nature'];
    $tiers_id = $_POST['tiers_id'];
    $montant = $_POST['montant'];
    
    $stmt = $pdo->prepare("INSERT INTO EFFETS_COMMERCE (numero_effet, date_creation, date_echeance, type_effet, nature, tiers_id, montant) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$numero, $date_creation, $date_echeance, $type_effet, $nature, $tiers_id, $montant]);
    
    // Écriture comptable : création de l'effet (client ou fournisseur)
    if ($nature == 'CLIENT') {
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 4111, 701, ?, ?, 'EFFET')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date_creation, "Vente avec effet $numero", $montant, $numero]);
    } else {
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 601, 4011, ?, ?, 'EFFET')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date_creation, "Achat avec effet $numero", $montant, $numero]);
    }
    $message = "✅ Effet créé et écriture comptable générée.";
}

// Escompte d'un effet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'escompter') {
    $effet_id = $_POST['effet_id'];
    $taux = $_POST['taux_escompte'];
    $date_escompte = $_POST['date_escompte'];
    $effet = $pdo->prepare("SELECT * FROM EFFETS_COMMERCE WHERE id = ?");
    $effet->execute([$effet_id]);
    $e = $effet->fetch();
    $jours = (strtotime($e['date_echeance']) - strtotime($date_escompte)) / (60*60*24);
    $interets = $e['montant'] * $taux / 100 * $jours / 360;
    $commission = $e['montant'] * 0.005;
    $agios = $interets + $commission;
    $montant_net = $e['montant'] - $agios;
    
    $update = $pdo->prepare("UPDATE EFFETS_COMMERCE SET statut = 'ESCOMPTE', taux_escompte = ?, frais_escompte = ?, agios = ?, commission = ?, montant_net = ?, banque_escompte = 521 WHERE id = ?");
    $update->execute([$taux, $interets, $agios, $commission, $montant_net, $effet_id]);
    
    // Écritures d'escompte
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
            (?, ?, 521, 4111, ?, ?, 'ESCOMPTE'),
            (?, ?, 631, 4111, ?, ?, 'ESCOMPTE'),
            (?, ?, 637, 4111, ?, ?, 'ESCOMPTE')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $date_escompte, "Escompte effet {$e['numero_effet']}", $montant_net, $e['numero_effet'],
        $date_escompte, "Agios effet {$e['numero_effet']}", $interets, $e['numero_effet'],
        $date_escompte, "Commission effet {$e['numero_effet']}", $commission, $e['numero_effet']
    ]);
    $message = "✅ Effet escompté - Net perçu : " . number_format($montant_net,0,',',' ') . " FCFA";
}

// Encaissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'encaisser') {
    $effet_id = $_POST['effet_id'];
    $date_encaissement = $_POST['date_encaissement'];
    $effet = $pdo->prepare("SELECT * FROM EFFETS_COMMERCE WHERE id = ?");
    $effet->execute([$effet_id]);
    $e = $effet->fetch();
    $update = $pdo->prepare("UPDATE EFFETS_COMMERCE SET statut = 'ENCAISSE' WHERE id = ?");
    $update->execute([$effet_id]);
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, 4111, ?, ?, 'ENCAISSEMENT')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_encaissement, "Encaissement effet {$e['numero_effet']}", $e['montant'], $e['numero_effet']]);
    $message = "✅ Effet encaissé";
}

$effets = $pdo->query("SELECT e.*, t.raison_sociale FROM EFFETS_COMMERCE e JOIN TIERS t ON e.tiers_id = t.id ORDER BY e.date_echeance ASC")->fetchAll();
$tiers = $pdo->query("SELECT * FROM TIERS WHERE type IN ('CLIENT','FOURNISSEUR')")->fetchAll();
?>
<!-- Le reste de l'interface HTML (identique à l'original) -->
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Effets de commerce</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<form method="POST" class="row g-3"><input type="hidden" name="action" value="creer_effet">
<div class="col-md-3"><label>Type</label><select name="type_effet" class="form-select"><option>LETTRE_CHANGE</option><option>BILLET_A_ORDRE</option></select></div>
<div class="col-md-3"><label>Nature</label><select name="nature" class="form-select"><option value="CLIENT">Client (effet à recevoir)</option><option value="FOURNISSEUR">Fournisseur (effet à payer)</option></select></div>
<div class="col-md-3"><label>Tiers</label><select name="tiers_id" class="form-select"><?php foreach($tiers as $t): ?><option value="<?= $t['id'] ?>"><?= $t['raison_sociale'] ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Date création</label><input type="date" name="date_creation" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="col-md-3"><label>Échéance</label><input type="date" name="date_echeance" class="form-control"></div>
<div class="col-md-3"><label>Montant</label><input type="number" name="montant" class="form-control" required></div>
<div class="col-md-3"><button type="submit" class="btn-omega">Créer effet</button></div></form>
<hr>
<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>N°</th><th>Création</th><th>Échéance</th><th>Type</th><th>Tiers</th><th>Montant</th><th>Statut</th><th>Actions</th></tr></thead>
<tbody><?php foreach($effets as $e): ?><tr><td><?= $e['numero_effet'] ?></td><td><?= $e['date_creation'] ?></td><td><?= $e['date_echeance'] ?></td><td><?= $e['type_effet'] ?></td><td><?= $e['raison_sociale'] ?></td><td class="text-end"><?= number_format($e['montant'],0,',',' ') ?> F</td>
<td><span class="badge bg-primary"><?= $e['statut'] ?></span></td>
<td><?php if($e['statut']=='EN_PORTEFEUILLE'): ?><button class="btn btn-sm btn-warning" onclick="escompter(<?= $e['id'] ?>, <?= $e['montant'] ?>)">Escompter</button> <button class="btn btn-sm btn-success" onclick="encaisser(<?= $e['id'] ?>)">Encaisser</button><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<script>function escompter(id,mt){let f=document.createElement('form');f.method='POST';f.innerHTML='<input type="hidden" name="action" value="escompter"><input type="hidden" name="effet_id" value="'+id+'"><input type="date" name="date_escompte" value="<?= date('Y-m-d') ?>"><input type="number" name="taux_escompte" placeholder="Taux %">';document.body.appendChild(f);f.submit();}</script>
<script>function encaisser(id){let f=document.createElement('form');f.method='POST';f.innerHTML='<input type="hidden" name="action" value="encaisser"><input type="hidden" name="effet_id" value="'+id+'"><input type="date" name="date_encaissement" value="<?= date('Y-m-d') ?>">';document.body.appendChild(f);f.submit();}</script>
<?php include 'inc_footer.php'; ?>
