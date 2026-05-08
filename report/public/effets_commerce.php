<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Effets de Commerce - Lettres de change, Billets à ordre";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Création d'un effet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'creer_effet') {
    $date_creation = $_POST['date_creation'];
    $date_echeance = $_POST['date_echeance'];
    $type_effet = $_POST['type_effet'];
    $nature = $_POST['nature'];
    $tiers_id = $_POST['tiers_id'];
    $montant = $_POST['montant'];
    
    $stmt = $pdo->prepare("INSERT INTO EFFETS_COMMERCE (numero_effet, date_creation, date_echeance, type_effet, nature, tiers_id, montant) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(["EFF-" . date('Ymd') . "-" . rand(100,999), $date_creation, $date_echeance, $type_effet, $nature, $tiers_id, $montant]);
    $message = "✅ Effet créé avec succès";
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
    
    $stmt = $pdo->prepare("UPDATE EFFETS_COMMERCE SET statut = 'ESCOMPTE', taux_escompte = ?, frais_escompte = ?, agios = ?, commission = ?, montant_net = ?, banque_escompte = 521 WHERE id = ?");
    $stmt->execute([$taux, $interets, $agios, $commission, $montant_net, $effet_id]);
    
    // Écriture d'escompte
    $ecriture = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'EFFET')");
    $ecriture->execute([$date_escompte, "Escompte effet n°" . $e['numero_effet'], 521, 411, $montant_net, $e['numero_effet'], 'EFFET']);
    
    $message = "✅ Effet escompté - Net perçu: " . number_format($montant_net, 0, ',', ' ') . " FCFA";
}

// Encaissement d'un effet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'encaisser') {
    $effet_id = $_POST['effet_id'];
    $date_encaissement = $_POST['date_encaissement'];
    
    $effet = $pdo->prepare("SELECT * FROM EFFETS_COMMERCE WHERE id = ?");
    $effet->execute([$effet_id]);
    $e = $effet->fetch();
    
    $stmt = $pdo->prepare("UPDATE EFFETS_COMMERCE SET statut = 'ENCAISSE' WHERE id = ?");
    $stmt->execute([$effet_id]);
    
    $ecriture = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'EFFET')");
    $ecriture->execute([$date_encaissement, "Encaissement effet n°" . $e['numero_effet'], 521, 411, $e['montant'], $e['numero_effet'], 'EFFET']);
    
    $message = "✅ Effet encaissé";
}

// Récupération des effets
$effets = $pdo->query("SELECT e.*, t.raison_sociale FROM EFFETS_COMMERCE e JOIN TIERS t ON e.tiers_id = t.id ORDER BY e.date_echeance ASC")->fetchAll();
$tiers = $pdo->query("SELECT * FROM TIERS WHERE type IN ('CLIENT', 'FOURNISSEUR')")->fetchAll();

$total_portefeuille = 0;
$total_escompte = 0;
foreach($effets as $e) {
    if($e['statut'] == 'EN_PORTEFEUILLE') $total_portefeuille += $e['montant'];
    if($e['statut'] == 'ESCOMPTE') $total_escompte += $e['montant_net'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> Effets de Commerce - LCR, Billet à ordre</h5>
                <small>Conformité SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Documents à présenter SYSCOHADA :</strong><br>
                    - Lettre de change relevé (LCR)<br>
                    - Billet à ordre<br>
                    - Traite<br>
                    - Promesse
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3"><div class="card bg-primary text-white text-center"><div class="card-body"><h4><?= count($effets) ?></h4><small>Effets traités</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-success text-white text-center"><div class="card-body"><h4><?= number_format($total_portefeuille, 0, ',', ' ') ?> F</h4><small>En portefeuille</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-warning text-dark text-center"><div class="card-body"><h4><?= number_format($total_escompte, 0, ',', ' ') ?> F</h4><small>Escomptés</small></div></div></div>
                    <div class="col-md-3"><div class="card bg-info text-white text-center"><div class="card-body"><button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newEffetModal">+ Nouvel effet</button></div></div></div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>N° effet</th><th>Date création</th><th>Échéance</th><th>Type</th><th>Tiers</th><th>Montant</th><th>Statut</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($effets as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['numero_effet']) ?></td>
                                <td><?= date('d/m/Y', strtotime($e['date_creation'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($e['date_echeance'])) ?></td>
                                <td><?= $e['type_effet'] ?></td>
                                <td><?= htmlspecialchars($e['raison_sociale']) ?></td>
                                <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                                <td><span class="badge <?= $e['statut'] == 'EN_PORTEFEUILLE' ? 'bg-primary' : ($e['statut'] == 'ESCOMPTE' ? 'bg-warning' : 'bg-success') ?>"><?= $e['statut'] ?></span></td>
                                <td>
                                    <?php if($e['statut'] == 'EN_PORTEFEUILLE'): ?>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#escompteModal" onclick="setEffetId(<?= $e['id'] ?>, <?= $e['montant'] ?>)">Escompter</button>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#encaissementModal" onclick="setEffetIdEnc(<?= $e['id'] ?>)">Encaisser</button>
                                    <?php endif; ?>
                                 </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création Effet -->
<div class="modal fade" id="newEffetModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5>➕ Nouvel effet de commerce</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="creer_effet">
    <div class="mb-2"><label>Type d'effet</label><select name="type_effet" class="form-select"><option value="LETTRE_CHANGE">Lettre de change (LCR)</option><option value="BILLET_A_ORDRE">Billet à ordre</option><option value="TRAITE">Traite</option></select></div>
    <div class="mb-2"><label>Nature</label><select name="nature" class="form-select"><option value="CLIENT">Client (effet à recevoir)</option><option value="FOURNISSEUR">Fournisseur (effet à payer)</option></select></div>
    <div class="mb-2"><label>Tiers</label><select name="tiers_id" class="form-select"><?php foreach($tiers as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['raison_sociale']) ?></option><?php endforeach; ?></select></div>
    <div class="mb-2"><label>Date création</label><input type="date" name="date_creation" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <div class="mb-2"><label>Date échéance</label><input type="date" name="date_echeance" class="form-control"></div>
    <div class="mb-2"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="1000" required></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-primary">Créer</button></div></form></div></div></div>

<script>
function setEffetId(id, montant) {
    document.querySelector('#escompteModal input[name="effet_id"]').value = id;
    document.querySelector('#escompteModal input[name="montant_effet"]').value = new Intl.NumberFormat().format(montant) + ' F';
}
function setEffetIdEnc(id) {
    document.querySelector('#encaissementModal input[name="effet_id"]').value = id;
}
</script>

<!-- Modal Escompte -->
<div class="modal fade" id="escompteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning"><h5>💰 Escompte d'effet</h5></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="escompter"><input type="hidden" name="effet_id" id="escompte_id">
    <div class="mb-2"><label>Montant de l'effet</label><input type="text" name="montant_effet" class="form-control" readonly></div>
    <div class="mb-2"><label>Taux d'escompte (%)</label><input type="number" name="taux_escompte" class="form-control" step="0.5" value="8"></div>
    <div class="mb-2"><label>Date d'escompte</label><input type="date" name="date_escompte" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <div class="alert alert-info">Calcul: Intérêts = Montant × Taux × Jours / 360 + Commission</div>
</div><div class="modal-footer"><button type="submit" class="btn btn-warning">Escompter</button></div></form></div></div></div>

<!-- Modal Encaissement -->
<div class="modal fade" id="encaissementModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5>💵 Encaissement d'effet</h5></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="encaisser"><input type="hidden" name="effet_id" id="encaissement_id">
    <div class="mb-2"><label>Date d'encaissement</label><input type="date" name="date_encaissement" class="form-control" value="<?= date('Y-m-d') ?>"></div>
</div><div class="modal-footer"><button type="submit" class="btn btn-success">Encaisser</button></div></form></div></div></div>

<?php include 'inc_footer.php'; ?>
