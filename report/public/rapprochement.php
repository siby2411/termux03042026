<?php
$page_title = "Rapprochement Bancaire";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/layout.php';

// Récupérer écritures comptables Banque (521) non pointées
$compta = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE (compte_debite_id = 521 OR compte_credite_id = 521) AND pointe = 0")->fetchAll();

// Récupérer lignes relevé bancaire non pointées
$banque = $pdo->query("SELECT * FROM releves_bancaires WHERE pointe = 0")->fetchAll();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Comptabilité (Livre de Banque)</div>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Libellé</th><th>Montant</th></tr></thead>
                    <tbody>
                        <?php foreach($compta as $c): ?>
                        <tr><td><?= $c['date_ecriture'] ?></td><td><?= $c['libelle'] ?></td><td><?= number_format($c['montant'],0) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">Relevé Bancaire (Banque)</div>
                <table class="table table-sm">
                    <thead><tr><th>Date</th><th>Libellé</th><th>Montant</th></tr></thead>
                    <tbody>
                        <?php foreach($banque as $b): ?>
                        <tr><td><?= $b['date_operation'] ?></td><td><?= $b['libelle'] ?></td><td><?= number_format($b['debit']+$b['credit'],0) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="pointage_auto.php" class="btn btn-dark">Lancer le Pointage Automatique</a>
    </div>
</div>
