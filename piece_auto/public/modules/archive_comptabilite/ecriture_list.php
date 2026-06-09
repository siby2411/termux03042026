<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Liste des écritures comptables";
$page_icon = "list-ul";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Vérifier les colonnes disponibles
$check = $pdo->query("SHOW COLUMNS FROM ECRITURES_COMPTABLES LIKE 'reference_piece'");
$has_ref = $check->rowCount() > 0;

$sql = "SELECT e.*, 
        c1.intitule_compte as lib_debit,
        c2.intitule_compte as lib_credit
        FROM ECRITURES_COMPTABLES e
        LEFT JOIN PLAN_COMPTABLE_UEMOA c1 ON e.compte_debite_id = c1.compte_id
        LEFT JOIN PLAN_COMPTABLE_UEMOA c2 ON e.compte_credite_id = c2.compte_id
        ORDER BY e.date_ecriture DESC, e.id DESC";
$stmt = $pdo->query($sql);
$entries = $stmt->fetchAll();

$total_montant = array_sum(array_column($entries, 'montant'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="bi bi-journal-bookmark-fill"></i> Journal des écritures</h5>
                    <small class="text-muted">Liste chronologique des opérations comptables</small>
                </div>
                <a href="ecriture.php" class="btn-omega btn-sm">
                    <i class="bi bi-plus-lg"></i> Nouvelle écriture
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Date</th>
                                <?php if($has_ref): ?>
                                <th>Référence</th>
                                <?php endif; ?>
                                <th>Libellé</th>
                                <th>Compte Débit</th>
                                <th>Intitulé Débit</th>
                                <th>Compte Crédit</th>
                                <th>Intitulé Crédit</th>
                                <th class="text-end">Montant (FCFA)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($entries) > 0): ?>
                                <?php foreach($entries as $entry): ?>
                                <tr>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($entry['date_ecriture'])) ?></td>
                                    <?php if($has_ref): ?>
                                    <td class="text-center"><?= htmlspecialchars($entry['reference_piece'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($entry['libelle'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?= $entry['compte_debite_id'] ?? '-' ?></span>
                                    </td>
                                    <td><small><?= htmlspecialchars(substr($entry['lib_debit'] ?? '-', 0, 40)) ?></small></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= $entry['compte_credite_id'] ?? '-' ?></span>
                                    </td>
                                    <td><small><?= htmlspecialchars(substr($entry['lib_credit'] ?? '-', 0, 40)) ?></small></td>
                                    <td class="text-end fw-bold text-primary"><?= number_format($entry['montant'], 0, ',', ' ') ?></td>
                                    <td class="text-center">
                                        <a href="ecriture_edit.php?id=<?= $entry['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $has_ref ? 9 : 8 ?>" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1"></i><br>
                                        Aucune écriture enregistrée. <a href="ecriture.php">Commencez la saisie</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="<?= $has_ref ? 7 : 6 ?>" class="text-end">TOTAL GÉNÉRAL :</td>
                                <td class="text-end text-primary"><?= number_format($total_montant, 0, ',', ' ') ?> F</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
