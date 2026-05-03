<?php
include 'includes/db.php';
include 'includes/header.php';

// Récupération des commandes avec le nom du créateur
$stmt = $pdo->query("SELECT c.*, u.nom as createur FROM commandes c LEFT JOIN users u ON c.cree_par_id = u.id ORDER BY c.date_commande DESC");
$commandes = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestion Facturation & Commandes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="creer_commande.php" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Commande
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>#ID</th>
                <th>Client</th>
                <th>Date</th>
                <th>Total HT</th>
                <th>État</th>
                <th>Créé par</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($commandes) > 0): ?>
                <?php foreach($commandes as $cmd): ?>
                <tr>
                    <td><?= $cmd['id'] ?></td>
                    <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cmd['date_commande'])) ?></td>
                    <td><?= number_format($cmd['total_ht'], 2, ',', ' ') ?> €</td>
                    <td>
                        <?php 
                            $badge = 'secondary';
                            if($cmd['etat'] == 'validee') $badge = 'info';
                            if($cmd['etat'] == 'facturee') $badge = 'success';
                            if($cmd['etat'] == 'annulee') $badge = 'danger';
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($cmd['etat']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($cmd['createur']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Aucune commande trouvée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
