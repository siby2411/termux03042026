<?php
require_once __DIR__ . '/../includes/db.php';
$page_title = "Balance Générale - OMEGA";
include "layout.php";

$sql = "SELECT p.compte_id, p.intitule_compte,
        SUM(CASE WHEN e.compte_debite_id = p.compte_id THEN e.montant ELSE 0 END) as total_debit,
        SUM(CASE WHEN e.compte_credite_id = p.compte_id THEN e.montant ELSE 0 END) as total_credit
        FROM PLAN_COMPTABLE_UEMOA p
        LEFT JOIN ECRITURES_COMPTABLES e ON (p.compte_id = e.compte_debite_id OR p.compte_id = e.compte_credite_id)
        GROUP BY p.compte_id
        HAVING total_debit > 0 OR total_credit > 0";
$comptes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-centered">
    <div class="card omega-card border-0 shadow-lg">
        <div class="card-header bg-white py-4 text-center">
            <h3 class="fw-bold text-dark text-uppercase">Balance de Vérification</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="bg-primary text-white text-center">
                    <tr>
                        <th rowspan="2" class="align-middle">Compte</th>
                        <th rowspan="2" class="align-middle">Intitulé</th>
                        <th colspan="2">Mouvements</th>
                        <th colspan="2">Soldes</th>
                    </tr>
                    <tr>
                        <th>Débit</th><th>Crédit</th>
                        <th>Débiteur</th><th>Créditeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($comptes as $c): 
                        $solde = $c['total_debit'] - $c['total_credit'];
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $c['compte_id'] ?></td>
                        <td><?= $c['intitule_compte'] ?></td>
                        <td class="text-end"><?= number_format($c['total_debit'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($c['total_credit'], 0, ',', ' ') ?></td>
                        <td class="text-end fw-bold text-primary"><?= $solde > 0 ? number_format($solde, 0, ',', ' ') : '-' ?></td>
                        <td class="text-end fw-bold text-danger"><?= $solde < 0 ? number_format(abs($solde), 0, ',', ' ') : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
