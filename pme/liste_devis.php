<?php
include 'includes/db.php';
include 'includes/header.php';

$devis = $pdo->query("SELECT d.*, c.nom as client_nom, p.designation FROM devis d JOIN clients c ON d.client_id = c.id JOIN produits p ON d.produit_id = p.id ORDER BY d.date_emission DESC")->fetchAll();
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3"><i class="fas fa-file-invoice-dollar text-warning me-2"></i>Gestion des Devis</h2>
        <a href="creer_devis.php" class="btn btn-dark"><i class="fas fa-plus"></i> Nouveau Devis</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Client</th><th>Produit</th><th>Total TTC</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($devis as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['client_nom']) ?></td>
                        <td><?= htmlspecialchars($d['designation']) ?> (x<?= $d['quantite'] ?>)</td>
                        <td><?= number_format($d['total_ht'] * 1.2, 2) ?> €</td>
                        <td>
                            <span class="badge rounded-pill <?= $d['statut']=='en_attente' ? 'bg-warning text-dark' : ($d['statut']=='accepte' ? 'bg-success' : 'bg-danger') ?>">
                                <?= ucfirst(str_replace('_', ' ', $d['statut'])) ?>
                            </span>
                        </td>
                        <td>
                            <a href="export_devis.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i></a>
                            <?php if($d['statut'] == 'en_attente'): ?>
                                <a href="transformer_devis.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Confirmer la transformation en commande ?')">
                                    <i class="fas fa-check"></i> Valider Vente
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
