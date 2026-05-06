<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Rapprochement bancaire";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des écritures bancaires (compte 521)
$sql = "
    SELECT e.*, 
           CASE WHEN e.compte_debite_id = 521 THEN 'Débit' ELSE 'Crédit' END as sens
    FROM ECRITURES_COMPTABLES e
    WHERE e.compte_debite_id = 521 OR e.compte_credite_id = 521
    ORDER BY e.date_ecriture DESC
";
$stmt = $pdo->query($sql);
$operations = $stmt->fetchAll();

$solde_bancaire = 0;
foreach($operations as $op) {
    if($op['compte_debite_id'] == 521) {
        $solde_bancaire += $op['montant'];
    } else {
        $solde_bancaire -= $op['montant'];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bank2"></i> Rapprochement bancaire - Compte 521</h5>
                <small class="text-muted">Lettrage des opérations bancaires vs relevé</small>
            </div>
            <div class="card-body">
                <!-- Solde actuel -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Solde bancaire actuel (compte 521) :</strong>
                    <span class="fs-3 fw-bold text-primary"><?= number_format($solde_bancaire, 0, ',', ' ') ?> FCFA</span>
                </div>
                
                <!-- Formulaire de saisie relevé -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6><i class="bi bi-upload"></i> Importer un relevé bancaire</h6>
                    </div>
                    <div class="card-body">
                        <form action="import_banque.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Date du relevé</label>
                                    <input type="date" name="date_releve" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label>Solde relevé (FCFA)</label>
                                    <input type="number" name="solde_releve" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label>Fichier relevé</label>
                                    <input type="file" name="fichier_releve" class="form-control" accept=".csv,.xlsx">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn-omega">
                                    <i class="bi bi-cloud-upload"></i> Importer et rapprocher
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Liste des opérations -->
                <h6><i class="bi bi-list-ul"></i> Opérations bancaires enregistrées</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Libellé</th>
                                <th>Référence</th>
                                <th>Type</th>
                                <th class="text-end">Montant</th>
                                <th>Pointé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($operations as $op): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($op['date_ecriture'])) ?></td>
                                <td><?= htmlspecialchars($op['libelle'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($op['reference_piece'] ?? '-') ?></td>
                                <td>
                                    <span class="badge <?= $op['sens'] == 'Débit' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $op['sens'] ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= number_format($op['montant'], 0, ',', ' ') ?></td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Écart de rapprochement -->
                <div class="card mt-3 bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <small>Solde comptable</small>
                                <h4 class="text-primary"><?= number_format($solde_bancaire, 0, ',', ' ') ?> F</h4>
                            </div>
                            <div class="col-md-4">
                                <small>Solde relevé (estimé)</small>
                                <h4 class="text-info">-</h4>
                            </div>
                            <div class="col-md-4">
                                <small>Écart</small>
                                <h4 class="text-warning">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
