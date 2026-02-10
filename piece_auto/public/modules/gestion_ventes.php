<?php
$page_title = "Gestion des Ventes";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT cv.*, c.nom_client, c.prenom_client 
          FROM COMMANDE_VENTE cv 
          JOIN CLIENTS c ON cv.id_client = c.id_client 
          ORDER BY cv.date_commande DESC";
$ventes = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<h1><i class="fas fa-file-invoice-dollar"></i> Historique des Ventes</h1>
<div class="mb-3">
    <a href="creer_commande_vente.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Vente</a>
</div>

<table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Client</th>
            <th class="text-end">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventes as $v): ?>
        <tr>
            <td>#<?= $v['id_commande_vente'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($v['date_commande'])) ?></td>
            <td><?= htmlspecialchars($v['nom_client'] . ' ' . $v['prenom_client']) ?></td>
            <td class="text-end fw-bold"><?= number_format($v['total_commande'], 2) ?> €</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
