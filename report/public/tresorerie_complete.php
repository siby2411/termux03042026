<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Trésorerie - Prévisions & Échéanciers";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Ajout d'un échéancier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_echeance') {
        $stmt = $pdo->prepare("INSERT INTO ECHEANCIERS_PAIEMENT (date_echeance, type_echeance, tiers_id, montant, libelle, reference_facture, mode_paiement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['date_echeance'], $_POST['type_echeance'], $_POST['tiers_id'], $_POST['montant'], $_POST['libelle'], $_POST['reference_facture'], $_POST['mode_paiement']]);
        $message = "✅ Échéance ajoutée";
    }
    
    if ($_POST['action'] === 'prevision_tresorerie') {
        $stmt = $pdo->prepare("INSERT INTO PREVISIONS_TRESORERIE (date_prevision, type_flux, montant, source) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['date_prevision'], $_POST['type_flux'], $_POST['montant'], $_POST['source']]);
        $message = "✅ Prévision ajoutée";
    }
}

$echeances = $pdo->query("
    SELECT e.*, t.raison_sociale as tiers_nom 
    FROM ECHEANCIERS_PAIEMENT e
    LEFT JOIN TIERS t ON e.tiers_id = t.id
    WHERE e.statut IN ('EN_ATTENTE', 'PARTIEL', 'EN_RETARD')
    ORDER BY e.date_echeance ASC
")->fetchAll();

$previsions = $pdo->query("SELECT * FROM PREVISIONS_TRESORERIE ORDER BY date_prevision ASC LIMIT 30")->fetchAll();
$solde_actuel = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) FROM ECRITURES_COMPTABLES")->fetchColumn();

// Calcul trésorerie prévisionnelle
$solde_previsionnel = $solde_actuel;
foreach($previsions as $p) {
    if($p['type_flux'] == 'ENCAISSEMENT') $solde_previsionnel += $p['montant'];
    else $solde_previsionnel -= $p['montant'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-cash-stack"></i> Trésorerie - Échéanciers & Prévisions</h5>
                <small>Pilotage des liquidités à court terme</small>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($solde_actuel, 0, ',', ' ') ?> F</h4>
                                <small>Solde bancaire actuel</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($solde_previsionnel, 0, ',', ' ') ?> F</h4>
                                <small>Solde prévisionnel</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= count($echeances) ?></h4>
                                <small>Échéances à venir</small>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="tresoTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#echeances">⏰ Échéanciers</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#previsions">📈 Prévisions</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouvelle">➕ Nouveau</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="echeances">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Échéance</th><th>Type</th><th>Tiers</th><th>Libellé</th>
                                    <th class="text-end">Montant (F)</th><th>Mode</th><th>Statut</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($echeances as $e): 
                                        $jours = (strtotime($e['date_echeance']) - strtotime(date('Y-m-d'))) / 86400;
                                        $row_class = $jours <= 2 ? 'table-danger' : ($jours <= 7 ? 'table-warning' : '');
                                    ?>
                                    <tr class="<?= $row_class ?>">
                                        <td class="text-center"><?= date('d/m/Y', strtotime($e['date_echeance'])) ?> 
                                            <?php if($jours <= 2): ?>⚠️<?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $e['type_echeance'] ?> </td>
                                        <td><?= htmlspecialchars($e['tiers_nom'] ?? '-') ?> </td>
                                        <td><?= htmlspecialchars($e['libelle']) ?> </td>
                                        <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= $e['mode_paiement'] ?> </td>
                                        <td class="text-center">
                                            <span class="badge <?= $e['statut'] == 'EN_ATTENTE' ? 'bg-warning' : ($e['statut'] == 'EN_RETARD' ? 'bg-danger' : 'bg-success') ?>">
                                                <?= $e['statut'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-success" onclick="paiementRapide(<?= $e['id'] ?>, <?= $e['montant'] ?>)">
                                                <i class="bi bi-check"></i> Payer
                                            </button>
                                         </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="previsions">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Type</th><th>Source</th><th class="text-end">Montant</th><th>Probabilité</th></tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cumul = $solde_actuel;
                                    foreach($previsions as $p): 
                                        $cumul += ($p['type_flux'] == 'ENCAISSEMENT') ? $p['montant'] : -$p['montant'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($p['date_prevision'])) ?> </td>
                                        <td class="text-center">
                                            <span class="badge <?= $p['type_flux'] == 'ENCAISSEMENT' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $p['type_flux'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($p['source']) ?> </td>
                                        <td class="text-end"><?= number_format($p['montant'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= $p['probabilite'] ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary fw-bold">
                                        <td> colspan="3" class="text-end">Solde prévisionnel après prévisions :</td>
                                        <td class="text-end"><?= number_format($cumul, 0, ',', ' ') ?> F</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="nouvelle">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">+ Nouvelle échéance</div>
                                    <div class="card-body">
                                        <form method="POST" class="row g-2">
                                            <input type="hidden" name="action" value="ajouter_echeance">
                                            <div class="col-md-6"><label>Date échéance</label><input type="date" name="date_echeance" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                                            <div class="col-md-6"><label>Type</label><select name="type_echeance" class="form-select"><option>CLIENT</option><option>FOURNISSEUR</option><option>SALAIRE</option><option>IMPOT</option></select></div>
                                            <div class="col-md-6"><label>Tiers</label><select name="tiers_id" class="form-select"><option value="">-- Sélectionner --</option><?php $tiers=$pdo->query("SELECT * FROM TIERS");foreach($tiers as $t):?><option value="<?= $t['id'] ?>"><?= $t['raison_sociale'] ?></option><?php endforeach; ?></select></div>
                                            <div class="col-md-6"><label>Mode paiement</label><select name="mode_paiement" class="form-select"><option>VIREMENT</option><option>CHEQUE</option><option>ESPECES</option><option>LCR</option><option>PRELEVEMENT</option></select></div>
                                            <div class="col-md-12"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                            <div class="col-md-8"><label>Référence facture</label><input type="text" name="reference_facture" class="form-control"></div>
                                            <div class="col-md-4"><label>Montant (F)</label><input type="number" name="montant" class="form-control" required></div>
                                            <div class="col-12"><button type="submit" class="btn-omega w-100">Créer échéance</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">+ Prévision de trésorerie</div>
                                    <div class="card-body">
                                        <form method="POST" class="row g-2">
                                            <input type="hidden" name="action" value="prevision_tresorerie">
                                            <div class="col-md-6"><label>Date</label><input type="date" name="date_prevision" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                                            <div class="col-md-6"><label>Type</label><select name="type_flux" class="form-select"><option value="ENCAISSEMENT">Encaissement</option><option value="DECAISSEMENT">Décaissement</option></select></div>
                                            <div class="col-md-12"><label>Source</label><input type="text" name="source" class="form-control" placeholder="Virement client, Facture fournisseur..."></div>
                                            <div class="col-md-12"><label>Montant (F)</label><input type="number" name="montant" class="form-control" required></div>
                                            <div class="col-12"><button type="submit" class="btn-omega w-100">Ajouter prévision</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paiementRapide(id, montant) {
    if(confirm(`Enregistrer le paiement de ${montant.toLocaleString()} F ?`)) {
        fetch(`paiement_rapide.php?id=${id}`).then(r => r.json()).then(data => {
            if(data.success) location.reload();
            else alert(data.error);
        });
    }
}
</script>

<?php include 'inc_footer.php'; ?>
