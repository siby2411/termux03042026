<?php
$page_title = "Mouvements de Stock";
require_once 'layout.php';
require_once __DIR__ . '/../config/database.php';

// Connexion normalisée
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
} else {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=synthesepro_db;charset=utf8mb4','root','123');
}

// Charger les mouvements de stock
$sql = "
    SELECT s.*, 
           pc1.intitule_compte AS debit_label,
           pc2.intitule_compte AS credit_label
    FROM ECRITURES_STOCK s
    LEFT JOIN PLAN_COMPTABLE_UEMOA pc1 ON pc1.compte_id = s.compte_debite_id
    LEFT JOIN PLAN_COMPTABLE_UEMOA pc2 ON pc2.compte_id = s.compte_credite_id
    ORDER BY s.date_operation DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Mouvements de Stock</h3>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Compte Débit</th>
                        <th>Compte Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $i): ?>
                    <tr>
                        <td><?= $i['date_operation'] ?></td>
                        <td><?= $i['type_enum'] ?></td>
                        <td><?= $i['quantite'] ?></td>
                        <td><?= $i['compte_debite_id'].' - '.$i['debit_label'] ?></td>
                        <td><?= $i['compte_credite_id'].' - '.$i['credit_label'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

