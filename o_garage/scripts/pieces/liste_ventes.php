<?php require_once '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-success">Journal des Ventes (Magasin)</h2>
    <a href="vente_dynamique.php" class="btn btn-success"><i class="fas fa-plus"></i> Nouvelle Vente</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Réf. Facture</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th class="text-end">Montant Total</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $res = $db->query("SELECT fp.*, c.nom FROM factures_pieces fp JOIN clients c ON fp.id_client = c.id_client ORDER BY fp.date_facture DESC");
                while($v = $res->fetch()): ?>
                <tr>
                    <td>#VP-<?= str_pad($v['id_facture'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['date_facture'])) ?></td>
                    <td class="fw-bold"><?= $v['nom'] ?></td>
                    <td class="text-end fw-bold text-primary"><?= number_format($v['total_vente'], 0, ',', ' ') ?> F</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-dark"><i class="fas fa-eye"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
