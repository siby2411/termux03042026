<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$page_title = "Bilan Synthétique - SYSCOHADA";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// ACTIF (Comptes de 2 à 5)
$sql_actif = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(e.montant), 0) as total
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON e.compte_debite_id = c.compte_id
    WHERE c.compte_id BETWEEN 200 AND 599
    GROUP BY c.compte_id, c.intitule_compte
    ORDER BY c.compte_id
";
$actifs = $pdo->query($sql_actif)->fetchAll();
$total_actif = array_sum(array_column($actifs, 'total'));

// PASSIF (Comptes de 1, 6, 7, 8 - côté crédit)
$sql_passif = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(e.montant), 0) as total
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON e.compte_credite_id = c.compte_id
    WHERE c.compte_id BETWEEN 100 AND 199 OR c.compte_id BETWEEN 600 AND 899
    GROUP BY c.compte_id, c.intitule_compte
    ORDER BY c.compte_id
";
$passifs = $pdo->query($sql_passif)->fetchAll();
$total_passif = array_sum(array_column($passifs, 'total'));
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart-fill"></i> Bilan SYSCOHADA</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-primary text-white">ACTIF (Emplois)</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($actifs as $a): ?>
                                    <tr><td><?= $a['compte_id'] ?> - <?= htmlspecialchars(substr($a['intitule_compte'], 0, 30)) ?></td>
                                    <td class="text-end"><?= number_format($a['total'], 0, ',', ' ') ?> F</td></tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary fw-bold">
                                        <td>TOTAL ACTIF</td>
                                        <td class="text-end"><?= number_format($total_actif, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">PASSIF (Ressources)</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($passifs as $p): ?>
                                    <tr><td><?= $p['compte_id'] ?> - <?= htmlspecialchars(substr($p['intitule_compte'], 0, 30)) ?></td>
                                    <td class="text-end"><?= number_format($p['total'], 0, ',', ' ') ?> F</td></tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold">
                                        <td>TOTAL PASSIF</td>
                                        <td class="text-end"><?= number_format($total_passif, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert <?= abs($total_actif - $total_passif) < 1 ? 'alert-success' : 'alert-danger' ?> mt-3">
                    <?php if(abs($total_actif - $total_passif) < 1): ?>
                        ✅ BILAN ÉQUILIBRÉ (<?= number_format($total_actif, 0, ',', ' ') ?> FCFA)
                    <?php else: ?>
                        ⚠️ Écart de <?= number_format(abs($total_actif - $total_passif), 0, ',', ' ') ?> FCFA
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
