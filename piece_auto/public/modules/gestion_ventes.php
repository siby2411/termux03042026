<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Historique des Ventes";
$database = new Database();
$db = $database->getConnection();

try {
    // Requête corrigée avec LEFT JOIN pour récupérer le nom et prénom du client
    $sql = "SELECT cv.*, c.nom, c.prenom 
            FROM COMMANDE_VENTE cv 
            LEFT JOIN CLIENTS c ON cv.id_client = c.id_client 
            ORDER BY cv.date_vente DESC";
    $ventes = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Historique des Ventes</h5>
    </div>
    <div class="card-body p-0">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger m-3"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventes)): ?>
                        <tr><td colspan="5" class="text-center py-4">Aucune vente enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventes as $v): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($v['id_commande_vente']) ?></td>
                                <td><?= htmlspecialchars($v['date_vente']) ?></td>
                                <td><?= htmlspecialchars(strtoupper($v['nom'] ?? 'Inconnu') . ' ' . ($v['prenom'] ?? '')) ?></td>
                                <td class="text-end fw-bold"><?= number_format($v['total_commande'], 0, ',', ' ') ?> F</td>
                                <td class="text-center">
                                    <a href="details_vente.php?id=<?= $v['id_commande_vente'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
