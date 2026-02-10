<?php
// /var/www/piece_auto/public/modules/gestion_commandes_achat.php
$page_title = "Suivi des Commandes d'Achat";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

try {
    // Requête pour lister les commandes avec le nom du fournisseur
    $query = "SELECT ca.*, f.nom_fournisseur 
              FROM COMMANDES_ACHAT ca
              JOIN FOURNISSEURS f ON ca.id_fournisseur = f.id_fournisseur
              ORDER BY ca.date_commande DESC";
    
    $stmt = $db->query($query);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
    $commandes = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-truck-loading"></i> Commandes Fournisseurs</h1>
    <a href="creation_commande_achat.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Commande
    </a>
</div>

<?= $message ?>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($commandes)): ?>
            <div class="alert alert-info mb-0">Aucune commande d'achat enregistrée pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Fournisseur</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $ca): ?>
                            <tr>
                                <td>#<?= $ca['id_commande_achat'] ?></td>
                                <td><?= date('d/m/Y', strtotime($ca['date_commande'])) ?></td>
                                <td><strong><?= htmlspecialchars($ca['nom_fournisseur']) ?></strong></td>
                                <td>
                                    <?php 
                                    $badge_class = 'bg-secondary';
                                    if ($ca['statut'] == 'En attente') $badge_class = 'bg-warning text-dark';
                                    if ($ca['statut'] == 'Reçue') $badge_class = 'bg-success';
                                    if ($ca['statut'] == 'Annulée') $badge_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $ca['statut'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if ($ca['statut'] == 'En attente'): ?>
                                            <a href="reception_achats.php?id=<?= $ca['id_commande_achat'] ?>" class="btn btn-sm btn-outline-success" title="Réceptionner">
                                                <i class="fas fa-check"></i> Réceptionner
                                            </a>
                                        <?php endif; ?>
                                        <a href="detail_commande_achat.php?id=<?= $ca['id_commande_achat'] ?>" class="btn btn-sm btn-outline-info" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <h5><i class="fas fa-lightbulb text-warning"></i> Aide au flux</h5>
    <p class="small text-muted">
        1. <strong>En attente</strong> : La commande est envoyée au fournisseur mais pas encore livrée.<br>
        2. <strong>Réceptionner</strong> : Cliquez sur ce bouton lorsque le camion arrive. Cela mettra à jour vos stocks et calculera le nouveau CUMP.
    </p>
</div>

<?php include '../../includes/footer.php'; ?>
