
<?php
$page_title = "Balance";
require_once "../includes/auth_check.php";
require_once "../includes/db.php";
require_once "layout.php";

$balance = $pdo->query("SELECT compte_id, intitule_compte,
                               SUM(CASE WHEN compte_debite_id=compte_id THEN montant ELSE 0 END) as debit,
                               SUM(CASE WHEN compte_credite_id=compte_id THEN montant ELSE 0 END) as credit
                        FROM PLAN_COMPTABLE_UEMOA 
                        LEFT JOIN ECRITURES_COMPTABLES ON compte_id=compte_debite_id OR compte_id=compte_credite_id
                        GROUP BY compte_id,intitule_compte")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card p-4 shadow-sm">
    <h5>Balance générale</h5>
    <table class="table table-bordered">
        <thead><tr><th>Compte</th><th>Intitulé</th><th>Débit</th><th>Crédit</th></tr></thead>
        <tbody>
            <?php foreach($balance as $b): ?>
                <tr>
                    <td><?= $b['compte_id'] ?></td>
                    <td><?= $b['intitule_compte'] ?></td>
                    <td><?= number_format($b['debit'],2,'.','') ?></td>
                    <td><?= number_format($b['credit'],2,'.','') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "footer.php"; ?>


