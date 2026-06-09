<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Compte de Résultat - SYSCOHADA";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupérer TOUS les comptes de produits et charges
$sql_produits = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(e.montant), 0) as total
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON e.compte_credite_id = c.compte_id
    WHERE c.compte_id BETWEEN 700 AND 799
    GROUP BY c.compte_id, c.intitule_compte
";
$produits = $pdo->query($sql_produits)->fetchAll();

$sql_charges = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(e.montant), 0) as total
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON e.compte_debite_id = c.compte_id
    WHERE c.compte_id BETWEEN 600 AND 699
    GROUP BY c.compte_id, c.intitule_compte
";
$charges = $pdo->query($sql_charges)->fetchAll();

$total_produits = array_sum(array_column($produits, 'total'));
$total_charges = array_sum(array_column($charges, 'total'));
$resultat_net = $total_produits - $total_charges;
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator-fill"></i> Compte de Résultat (SYSCOHADA UEMOA)</h5>
                <small>Exercice <?= date('Y') ?> - En FCFA</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">PRODUITS (Classe 7)</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($produits as $p): ?>
                                    <tr><td><?= $p['compte_id'] ?> - <?= htmlspecialchars($p['intitule_compte']) ?></td>
                                    <td class="text-end"><?= number_format($p['total'], 0, ',', ' ') ?> F</td></tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold">
                                        <td>TOTAL PRODUITS</td>
                                        <td class="text-end"><?= number_format($total_produits, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-danger text-white">CHARGES (Classe 6)</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($charges as $c): ?>
                                    <tr><td><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></td>
                                    <td class="text-end"><?= number_format($c['total'], 0, ',', ' ') ?> F</td></tr>
                                    <?php endforeach; ?>
                                    <tr class="table-danger fw-bold">
                                        <td>TOTAL CHARGES</td>
                                        <td class="text-end"><?= number_format($total_charges, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert <?= $resultat_net >= 0 ? 'alert-success' : 'alert-danger' ?> mt-3 text-center">
                    <h4>RÉSULTAT NET : <?= number_format(abs($resultat_net), 0, ',', ' ') ?> FCFA <?= $resultat_net >= 0 ? '(BÉNÉFICE)' : '(PERTE)' ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
