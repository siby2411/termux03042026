<?php
// 1. Inclusions avec les bons chemins (on remonte de 2 niveaux : modules -> public -> racine)
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Gestion des Ventes";
include '../../includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Requête SQL
    $query = "SELECT cv.*, c.nom_client, c.prenom_client 
              FROM COMMANDE_VENTE cv 
              JOIN CLIENTS c ON cv.id_client = c.id_client 
              ORDER BY cv.date_commande DESC";
    
    $stmt = $db->query($query);
    $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur de base de données : " . $e->getMessage() . "</div>";
    $ventes = []; // On évite l'erreur dans le foreach plus bas
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-invoice-dollar text-primary"></i> Historique des Ventes</h1>
        <a href="creer_commande_vente.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Nouvelle Vente
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
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
                    <?php if (empty($ventes)): ?>
                        <tr><td colspan="5" class="text-center py-4">Aucune vente enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventes as $v): ?>
                        <tr>
                            <td>#<?= $v['id_commande_vente'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($v['date_commande'])) ?></td>
                            <td><?= htmlspecialchars($v['nom_client'] . ' ' . $v['prenom_client']) ?></td>
                            <td class="text-end fw-bold"><?= number_format($v['total_commande'], 2, ',', ' ') ?> €</td>
                            <td class="text-center">
                                <a href="details_vente.php?id=<?= $v['id_commande_vente'] ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
