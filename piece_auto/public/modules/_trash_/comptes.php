

<?php
$page_title = "Comptes";
require_once "../includes/auth_check.php";
require_once "../includes/db.php";
require_once "layout.php";

$comptes = $pdo->query("SELECT * FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card p-4 shadow-sm">
    <h5>Plan comptable UEMOA</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>ID</th><th>Intitulé</th><th>Classe</th><th>Solde</th><th>Nature</th></tr>
        </thead>
        <tbody>
            <?php foreach($comptes as $c): ?>
                <tr>
                    <td><?= $c['compte_id'] ?></td>
                    <td><?= $c['intitule_compte'] ?></td>
                    <td><?= $c['classe'] ?></td>
                    <td><?= $c['solde_normal'] ?></td>
                    <td><?= $c['nature_resultat'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "footer.php"; ?>

