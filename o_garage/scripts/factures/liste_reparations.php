<?php require_once '../../includes/header.php'; ?>
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span class="fw-bold">HISTORIQUE DES FACTURES RÉPARATION</span>
            <span class="badge bg-danger">Total : 
                <?php echo number_format($db->query("SELECT SUM(montant_total) FROM factures_reparation")->fetchColumn(), 0, '.', ' '); ?> F
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>Client / Plaque</th>
                        <th>Prestation</th>
                        <th>Montant Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT fr.*, c.nom, v.immatriculation, p.libelle 
                            FROM factures_reparation fr
                            JOIN fiches_intervention fi ON fr.id_fiche = fi.id_fiche
                            JOIN vehicules v ON fi.id_vehicule = v.id_vehicule
                            JOIN clients c ON v.id_client = c.id_client
                            JOIN prestations p ON fr.id_prestation = p.id_prestation
                            ORDER BY fr.date_facture DESC";
                    $res = $db->query($sql);
                    while($r = $res->fetch()): ?>
                    <tr>
                        <td class="fw-bold text-danger">#FAC-REP-<?= $r['id_facture'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['date_facture'])) ?></td>
                        <td><?= $r['nom'] ?> <br><small class="badge bg-secondary"><?= $r['immatriculation'] ?></small></td>
                        <td><?= $r['libelle'] ?></td>
                        <td class="fw-bold"><?= number_format($r['montant_total'], 0, '.', ' ') ?> F</td>
                        <td>
                            <a href="imprimer.php?id=<?= $r['id_facture'] ?>" class="btn btn-sm btn-outline-dark"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
