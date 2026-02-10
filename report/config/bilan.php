<?php
$page_title = "Bilan";
require_once 'layout.php';
require_once '../config/database.php'; // $conn disponible

// Récupération des données de la synthèse balance
$stmt = $conn->prepare("SELECT sb.*, pc.intitule_compte 
                        FROM SYNTHESES_BALANCE sb
                        LEFT JOIN PLAN_COMPTABLE_UEMOA pc ON sb.compte_id = pc.compte_id
                        ORDER BY sb.compte_id");
$stmt->execute();
$balances = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Bilan</h3>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Compte</th>
                        <th>Intitulé</th>
                        <th>Débit</th>
                        <th>Crédit</th>
                        <th>Solde Débiteur</th>
                        <th>Solde Créditeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($balances as $b): ?>
                    <tr>
                        <td><?= $b['compte_id'] ?></td>
                        <td><?= htmlspecialchars($b['intitule_compte']) ?></td>
                        <td><?= number_format($b['mouvement_debit'],2,',',' ') ?></td>
                        <td><?= number_format($b['mouvement_credit'],2,',',' ') ?></td>
                        <td><?= number_format($b['solde_debiteur'],2,',',' ') ?></td>
                        <td><?= number_format($b['solde_crediteur'],2,',',' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



