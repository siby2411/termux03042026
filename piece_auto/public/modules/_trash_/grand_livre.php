<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Grand Livre - Consultation analytique";
$page_icon = "book";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des comptes
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();

$filtre = isset($_GET['compte']) ? (int)$_GET['compte'] : null;

// Requête des écritures
$sql = "SELECT e.*, 
        c1.intitule_compte as lib_debit,
        c2.intitule_compte as lib_credit
        FROM ECRITURES_COMPTABLES e
        LEFT JOIN PLAN_COMPTABLE_UEMOA c1 ON e.compte_debite_id = c1.compte_id
        LEFT JOIN PLAN_COMPTABLE_UEMOA c2 ON e.compte_credite_id = c2.compte_id";

if ($filtre) {
    $sql .= " WHERE e.compte_debite_id = :filtre OR e.compte_credite_id = :filtre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['filtre' => $filtre]);
} else {
    $stmt = $pdo->query($sql);
}
$ecritures = $stmt->fetchAll();

$total_debit = 0;
$total_credit = 0;
foreach ($ecritures as $row) {
    if ($row['compte_debite_id']) $total_debit += $row['montant'];
    if ($row['compte_credite_id']) $total_credit += $row['montant'];
}
?>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0"><i class="bi bi-book-half"></i> Grand Livre Général</h5>
            <form method="GET" class="d-flex gap-2">
                <select name="compte" class="form-select" onchange="this.form.submit()">
                    <option value="">📊 Tous les comptes</option>
                    <?php foreach ($comptes as $c): ?>
                        <option value="<?= $c['compte_id'] ?>" <?= $filtre == $c['compte_id'] ? 'selected' : '' ?>>
                            <?= $c['compte_id'] ?> - <?= htmlspecialchars(substr($c['intitule_compte'], 0, 40)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filtre): ?>
                    <a href="grand_livre.php" class="btn btn-outline-secondary btn-sm">✖ Réinitialiser</a>
                <?php endif; ?>
            </form>
            <div>
                <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer"></i></button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Libellé</th>
                        <th>Compte Débit</th>
                        <th class="text-danger">Débit (FCFA)</th>
                        <th>Compte Crédit</th>
                        <th class="text-success">Crédit (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ecritures) > 0): ?>
                        <?php foreach ($ecritures as $row): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y', strtotime($row['date_ecriture'])) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['reference_piece'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['libelle'] ?? '-') ?></td>
                                <td class="text-center">
                                    <?php if ($row['compte_debite_id']): ?>
                                        <span class="badge bg-danger"><?= $row['compte_debite_id'] ?></span>
                                        <br><small><?= htmlspecialchars(substr($row['lib_debit'] ?? '', 0, 25)) ?></small>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td class="text-end text-danger fw-bold"><?= $row['compte_debite_id'] ? number_format($row['montant'], 0, ',', ' ') : '-' ?></td>
                                <td class="text-center">
                                    <?php if ($row['compte_credite_id']): ?>
                                        <span class="badge bg-success"><?= $row['compte_credite_id'] ?></span>
                                        <br><small><?= htmlspecialchars(substr($row['lib_credit'] ?? '', 0, 25)) ?></small>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td class="text-end text-success fw-bold"><?= $row['compte_credite_id'] ? number_format($row['montant'], 0, ',', ' ') : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Aucune écriture trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="4" class="text-end">TOTAUX :</td>
                        <td class="text-end text-danger"><?= number_format($total_debit, 0, ',', ' ') ?> F</td>
                        <td></td>
                        <td class="text-end text-success"><?= number_format($total_credit, 0, ',', ' ') ?> F</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
