<?php
// /var/www/piece_auto/public/modules/gestion_commandes_vente.php
$page_title = "Gestion des Commandes de Vente";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

try {
    $query = "SELECT 
                cv.id_commande_vente, 
                cv.date_commande, 
                cv.total_commande, 
                c.nom, 
                c.prenom 
              FROM COMMANDE_VENTE cv
              JOIN CLIENTS c ON cv.id_client = c.id_client
              ORDER BY cv.date_commande DESC";
    
    $stmt = $db->query($query);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
    $commandes = [];
}
?>

<h1><i class="fas fa-file-invoice-dollar"></i> <?= $page_title ?></h1>
<hr>

<?= $message ?>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($commandes)): ?>
            <div class="alert alert-info">Aucune vente enregistrée.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $cmd): ?>
                            <tr>
                                <td>#<?= $cmd['id_commande_vente'] ?></td>
                                <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                                <td><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></td>
                                <td class="text-end fw-bold"><?= number_format($cmd['total_commande'], 2, ',', ' ') ?> €</td>
                                <td class="text-center">
                                    <a href="imprimer_facture.php?id=<?= $cmd['id_commande_vente'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print"></i> Facture Pro
                                    </a>
                                    <a href="detail_commande_vente.php?id=<?= $cmd['id_commande_vente'] ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-file-invoice-dollar"></i> Historique des Ventes</h1>
    <a href="export_ventes.php" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Exporter pour Comptabilité (CSV)
    </a>
</div>



<?php include '../../includes/footer.php'; ?>
