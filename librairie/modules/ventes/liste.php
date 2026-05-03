<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Historique des ventes';

// Récupérer les ventes
$ventes = $pdo->query("
    SELECT v.*, u.username as caissier, 
           CONCAT(c.prenom, ' ', c.nom) as client_nom
    FROM ventes v
    JOIN utilisateurs u ON v.utilisateur_id = u.id
    LEFT JOIN clients c ON v.client_id = c.id
    ORDER BY v.date_vente DESC
    LIMIT 100
")->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-history"></i> Historique des ventes</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="table_ventes">
                        <thead>
                             <tr>
                                <th>Facture</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Caissier</th>
                                <th>Montant</th>
                                <th>Paiement</th>
                                <th>Statut</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ventes as $vente): ?>
                             <tr>
                                <td><?php echo $vente['numero_facture']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                                <td><?php echo $vente['client_nom'] ?? 'Anonyme'; ?></td>
                                <td><?php echo $vente['caissier']; ?></td>
                                <td><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                <td><?php echo ucfirst($vente['mode_paiement']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $vente['statut'] == 'validee' ? 'success' : 'danger'; ?>">
                                        <?php echo $vente['statut']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="facture.php?id=<?php echo $vente['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-print"></i> Facture
                                    </a>
                                </td>
                             </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
