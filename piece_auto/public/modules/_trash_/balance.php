<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Balance générale";
$page_icon = "scale";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Calcul de la balance par compte
$sql = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) as total_debit,
        COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0) as total_credit
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id)
    GROUP BY c.compte_id, c.intitule_compte
    ORDER BY c.compte_id
";
$stmt = $pdo->query($sql);
$balances = $stmt->fetchAll();

$total_general_debit = 0;
$total_general_credit = 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-scale"></i> Balance générale des comptes</h5>
                <small class="text-muted">Arrêté au <?= date('d/m/Y') ?> - En FCFA</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>N° Compte</th>
                                <th>Intitulé du compte</th>
                                <th>Total Débit (FCFA)</th>
                                <th>Total Crédit (FCFA)</th>
                                <th>Solde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($balances as $row): 
                                $solde = $row['total_debit'] - $row['total_credit'];
                                $total_general_debit += $row['total_debit'];
                                $total_general_credit += $row['total_credit'];
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $row['compte_id'] ?></td>
                                <td><?= htmlspecialchars($row['intitule_compte']) ?></td>
                                <td class="text-end text-danger"><?= number_format($row['total_debit'], 0, ',', ' ') ?></td>
                                <td class="text-end text-success"><?= number_format($row['total_credit'], 0, ',', ' ') ?></td>
                                <td class="text-end fw-bold <?= $solde >= 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= number_format(abs($solde), 0, ',', ' ') ?>
                                    <?= $solde >= 0 ? 'Débiteur' : 'Créditeur' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">TOTAUX GÉNÉRAUX :</td>
                                <td class="text-end text-danger"><?= number_format($total_general_debit, 0, ',', ' ') ?></td>
                                <td class="text-end text-success"><?= number_format($total_general_credit, 0, ',', ' ') ?></td>
                                <td class="text-end">
                                    <?php if($total_general_debit == $total_general_credit): ?>
                                        <span class="badge bg-success">✓ ÉQUILIBRE PARFAIT</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">✗ DÉSÉQUILIBRE</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if($total_general_debit != $total_general_credit): ?>
        <div class="alert alert-danger mt-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Attention :</strong> La balance n'est pas équilibrée ! Différence de 
            <?= number_format(abs($total_general_debit - $total_general_credit), 0, ',', ' ') ?> FCFA.
        </div>
        <?php else: ?>
        <div class="alert alert-success mt-3">
            <i class="bi bi-check-circle-fill"></i>
            <strong>Balance équilibrée :</strong> Le principe de la partie double est respecté. Total Débit = Total Crédit.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
