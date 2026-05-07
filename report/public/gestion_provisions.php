<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Provisions et Dépréciations - SYSCOHADA";
$page_icon = "shield";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Constitution d'une provision
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'constituer_provision') {
        $stmt = $pdo->prepare("INSERT INTO PROVISIONS_DEPRECIATIONS (date_constitution, libelle, type_provision, sous_type, compte_dotation, compte_provision, montant_initial, montant_actuel, justificatif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['date_constitution'], $_POST['libelle'], $_POST['type_provision'],
            $_POST['sous_type'], $_POST['compte_dotation'], $_POST['compte_provision'],
            $_POST['montant'], $_POST['montant'], $_POST['justificatif']
        ]);
        
        // Écriture comptable de dotation
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'PROVISION')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['date_constitution'], "Dotation provision - " . $_POST['libelle'], $_POST['compte_dotation'], $_POST['compte_provision'], $_POST['montant'], "PROV-" . date('Ymd')]);
        
        $message = "✅ Provision constituée avec succès";
    }
    
    if ($_POST['action'] === 'reprendre_provision') {
        $stmt = $pdo->prepare("UPDATE PROVISIONS_DEPRECIATIONS SET statut = 'REPRISE', date_reprise = CURDATE(), montant_reprise = montant_actuel WHERE id = ?");
        $stmt->execute([$_POST['provision_id']]);
        $message = "✅ Provision reprise";
    }
}

// Récupération des provisions
$provisions = $pdo->query("SELECT * FROM PROVISIONS_DEPRECIATIONS WHERE statut = 'ACTIVE' ORDER BY date_constitution DESC")->fetchAll();
$provisions_cloturees = $pdo->query("SELECT * FROM PROVISIONS_DEPRECIATIONS WHERE statut != 'ACTIVE' ORDER BY date_constitution DESC")->fetchAll();

$total_provisions = array_sum(array_column($provisions, 'montant_actuel'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-shield"></i> Provisions et Dépréciations - SYSCOHADA UEMOA</h5>
                <small>Conformément à l'Acte Uniforme OHADA</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Fondements juridiques (Acte Uniforme OHADA - Art. 13 à 16) :</strong><br>
                    - Provisions pour risques : Passifs dont l'échéance ou le montant est incertain<br>
                    - Provisions pour charges : Charges dont la réalisation est probable<br>
                    - Dépréciations d'actifs : Moins-values latentes sur actifs circulants
                </div>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body"><h4><?= count($provisions) ?></h4><small>Provisions actives</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body"><h4><?= number_format($total_provisions, 0, ',', ' ') ?> F</h4><small>Montant total</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body"><button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newProvisionModal">➕ Nouvelle provision</button></div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des provisions -->
                <h6><i class="bi bi-list-check"></i> Provisions actives</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark"><tr><th>Date</th><th>Libellé</th><th>Type</th><th>Compte débit</th><th>Compte crédit</th><th class="text-end">Montant</th><th>Actions</th></tr></thead>
                        <tbody><?php foreach($provisions as $p): ?>
                        <tr><td><?= date('d/m/Y', strtotime($p['date_constitution'])) ?></td><td><?= htmlspecialchars($p['libelle']) ?></td><td><?= $p['type_provision'] ?></td><td class="text-center"><?= $p['compte_dotation'] ?></td><td class="text-center"><?= $p['compte_provision'] ?></td><td class="text-end"><?= number_format($p['montant_actuel'], 0, ',', ' ') ?> F</td>
                        <td class="text-center"><form method="POST" style="display:inline"><input type="hidden" name="action" value="reprendre_provision"><input type="hidden" name="provision_id" value="<?= $p['id'] ?>"><button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reprendre cette provision ?')">Reprendre</button></form></td></tr>
                        <?php endforeach; ?></tbody>
                        <tfoot class="table-secondary"><tr><td colspan="5" class="text-end fw-bold">TOTAL :</td><td class="text-end fw-bold"><?= number_format($total_provisions, 0, ',', ' ') ?> F</td><td></td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal nouvelle provision -->
<div class="modal fade" id="newProvisionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Constituer une provision</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="constituer_provision"><div class="mb-3"><label>Date</label><input type="date" name="date_constitution" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="mb-3"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
<div class="mb-3"><label>Type de provision</label><select name="type_provision" class="form-select"><option value="RISQUES">Risques</option><option value="CHARGES">Charges</option><option value="DEPRECIATION_ACTIF">Dépréciation d'actif</option></select></div>
<div class="mb-3"><label>Sous-type</label><input type="text" name="sous_type" class="form-control" placeholder="Ex: Litige, Créance douteuse"></div>
<div class="mb-3"><label>Compte de dotation (68)</label><input type="number" name="compte_dotation" class="form-control" value="681" required></div>
<div class="mb-3"><label>Compte de provision (16)</label><input type="number" name="compte_provision" class="form-control" value="161" required></div>
<div class="mb-3"><label>Montant (FCFA)</label><input type="number" name="montant" class="form-control" step="1000" required></div>
<div class="mb-3"><label>Justificatif</label><textarea name="justificatif" class="form-control" rows="2"></textarea></div></div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger">Constituer</button></div></form></div></div></div>

<?php include 'inc_footer.php'; ?>
