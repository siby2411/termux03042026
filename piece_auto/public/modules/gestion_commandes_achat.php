<?php
// Active les erreurs pour voir le coupable exact sous Termux
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Suivi des Commandes d'Achat";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';
$commandes = [];

try {
    // Vérification de la structure SQL pour éviter la page blanche
    $query = "SELECT ca.*, f.nom_fournisseur 
              FROM COMMANDES_ACHAT ca
              JOIN FOURNISSEURS f ON ca.id_fournisseur = f.id_fournisseur
              ORDER BY ca.date_commande DESC";
    
    $stmt = $db->query($query);
    if ($stmt) {
        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger"><b>Erreur SQL :</b> ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-truck-loading text-primary"></i> Commandes Fournisseurs</h1>
        <a href="creation_commande_achat.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Nouvelle Commande
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (empty($commandes) && empty($message)): ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> Aucune commande d'achat enregistrée pour le moment.
                </div>
            <?php elseif (!empty($commandes)): ?>
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
                                    <td>#<?= $ca['id_commande_achat'] ?? 'N/A' ?></td>
                                    <td><?= date('d/m/Y', strtotime($ca['date_commande'])) ?></td>
                                    <td><strong><?= htmlspecialchars($ca['nom_fournisseur'] ?? 'Inconnu') ?></strong></td>
                                    <td>
                                        <?php 
                                        $statut = $ca['statut'] ?? 'En attente';
                                        $badge = ($statut == 'Reçue') ? 'bg-success' : (($statut == 'Annulée') ? 'bg-danger' : 'bg-warning text-dark');
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= $statut ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="detail_commande_achat.php?id=<?= $ca['id_commande_achat'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($statut !== 'Reçue'): ?>
                                                <a href="reception_achats.php?id=<?= $ca['id_commande_achat'] ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
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
</div>

<?php include '../../includes/footer.php'; ?>
