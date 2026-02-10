<?php
$page_title = "Liste des Stocks";
require_once 'layout.php';
require_once '../config/database.php'; // $conn disponible

// Récupération des données du stock
$stmt = $conn->prepare("SELECT s.*, e.designation AS immo_designation
                        FROM STOCK s
                        LEFT JOIN IMMOBILISATIONS e ON s.stock_id = e.immo_id
                        ORDER BY s.stock_id");
$stmt->execute();
$stocks = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Liste des Stocks</h3>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID Stock</th>
                        <th>Désignation</th>
                        <th>Quantité</th>
                        <th>Date Opération</th>
                        <th>Compte Débit</th>
                        <th>Compte Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($stocks as $s): ?>
                    <tr>
                        <td><?= $s['stock_id'] ?></td>
                        <td><?= htmlspecialchars($s['designation'] ?? $s['immo_designation']) ?></td>
                        <td><?= number_format($s['quantite'],2,',',' ') ?></td>
                        <td><?= $s['date_operation'] ?></td>
                        <td><?= $s['compte_debite_id'] ?></td>
                        <td><?= $s['compte_credite_id'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

