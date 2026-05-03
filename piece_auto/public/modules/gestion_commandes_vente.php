<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "Historique des Ventes";
include '../../includes/header.php';

// Correction : Utilisation de date_vente au lieu de date_commande
$query = "SELECT cv.id_commande_vente, cv.date_vente, cv.total_commande, cv.vin_vehicule, 
                 c.nom, c.prenom, c.telephone 
          FROM COMMANDE_VENTE cv 
          JOIN CLIENTS c ON cv.id_client = c.id_client 
          ORDER BY cv.date_vente DESC";
$stmt = $db->query($query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fas fa-history text-primary"></i> Journal des Ventes</h3>
    <a href="creer_commande_vente.php" class="btn btn-success">
        <i class="fas fa-plus"></i> Nouvelle Vente
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th>VIN Véhicule</th>
                    <th class="text-end">Montant Total</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($v = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><span class="badge bg-light text-dark">#<?= $v['id_commande_vente'] ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['date_vente'])) ?></td>
                    <td>
                        <div class="fw-bold"><?= strtoupper($v['nom']) ?> <?= $v['prenom'] ?></div>
                        <small class="text-muted"><?= $v['telephone'] ?></small>
                    </td>
                    <td><code><?= $v['vin_vehicule'] ?: 'N/A' ?></code></td>
                    <td class="text-end fw-bold text-primary"><?= number_format($v['total_commande'], 0, ',', ' ') ?> F</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="generate_invoice.php?id=<?= $v['id_commande_vente'] ?>" class="btn btn-sm btn-outline-info" title="Voir Facture">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            <a href="detail_commande_vente.php?id=<?= $v['id_commande_vente'] ?>" class="btn btn-sm btn-outline-secondary" title="Détails">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($stmt->rowCount() == 0): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3"></i><br>Aucune vente enregistrée pour le moment.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
