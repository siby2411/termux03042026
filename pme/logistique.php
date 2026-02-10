<?php
include 'includes/db.php';
include 'includes/header.php';

// Commandes à préparer (Etat = 'validee')
$a_expedier = $pdo->query("SELECT * FROM commandes WHERE etat = 'validee'")->fetchAll();
?>

<h1 class="h2 border-bottom pb-2 text-success"><i class="fas fa-truck"></i> Logistique & Expéditions</h1>

<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle"></i> Ce module gère les préparations de commande. Pour l'inventaire, voir <a href="stock.php">Gestion des Stocks</a>.
</div>

<h3 class="mt-4">Bons de Livraison à préparer</h3>
<table class="table table-bordered mt-3">
    <thead class="table-success">
        <tr>
            <th>N° Commande</th>
            <th>Client</th>
            <th>Date Commande</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(count($a_expedier) > 0): ?>
            <?php foreach($a_expedier as $cmd): ?>
            <tr>
                <td>#<?= $cmd['id'] ?></td>
                <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></td>
                <td>
                    <button class="btn btn-success btn-sm">
                        <i class="fas fa-check"></i> Marquer Expédié
                    </button>
                    <a href="#" class="btn btn-outline-dark btn-sm"><i class="fas fa-print"></i> Imprimer BL</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">Aucune commande en attente d'expédition.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
