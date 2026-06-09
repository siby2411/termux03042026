<?php
$page_title = "Liste des Écritures";
require_once 'layout.php';
require_once __DIR__ . '/../config/database.php';

// Connexion normalisée (copié de bilan.php)
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
} else {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=synthesepro_db;charset=utf8mb4','root','123');
}

// Charger les écritures
$sql = "
    SELECT e.*, pc1.intitule_compte AS compte_debit_label,
                 pc2.intitule_compte AS compte_credit_label
    FROM ECRITURES_COMPTABLES e
    LEFT JOIN PLAN_COMPTABLE_UEMOA pc1 ON pc1.compte_id = e.compte_debite_id
    LEFT JOIN PLAN_COMPTABLE_UEMOA pc2 ON pc2.compte_id = e.compte_credite_id
    ORDER BY e.date_operation DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Liste des Écritures Comptables</h3>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th>Compte Débité</th>
                        <th>Compte Crédité</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($rows as $r): ?>
                    <tr>
                        <td><?= $r['date_operation'] ?></td>
                        <td><?= htmlspecialchars($r['libelle_operation']) ?></td>
                        <td><?= $r['compte_debite_id'].' - '.$r['compte_debit_label'] ?></td>
                        <td><?= $r['compte_credite_id'].' - '.$r['compte_credit_label'] ?></td>
                        <td><?= number_format($r['montant'],2,',',' ') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

