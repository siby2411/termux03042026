<?php require_once '../../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4 fw-bold text-primary">Analyse Détaillée par Département</h2>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-primary text-white"><h5><i class="fas fa-tools me-2"></i>Détail Réparations (Main d'œuvre)</h5></div>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Date</th><th>Véhicule</th><th>Mécanicien</th><th>Prestation</th><th class="text-end">Montant</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $db->query("SELECT f.*, v.immatriculation, p.nom_complet FROM fiches_intervention f JOIN vehicules v ON f.id_vehicule=v.id_vehicule JOIN personnel p ON f.id_mec_1=p.id_personnel WHERE f.statut='Terminé'");
                    while($f = $res->fetch()): ?>
                    <tr>
                        <td><?= $f['date_entree'] ?></td>
                        <td><span class="badge bg-dark"><?= $f['immatriculation'] ?></span></td>
                        <td><?= $f['nom_complet'] ?></td>
                        <td><?= $f['description_panne'] ?></td>
                        <td class="text-end fw-bold"><?= number_format($f['cout_main_doeuvre'], 0, ',', ' ') ?> F</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-success text-white"><h5><i class="fas fa-box me-2"></i>Détail Ventes Pièces Détachées</h5></div>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Date</th><th>Client</th><th class="text-end">Total Vente</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $db->query("SELECT fp.*, c.nom FROM factures_pieces fp JOIN clients c ON fp.id_client=c.id_client");
                    while($fp = $res->fetch()): ?>
                    <tr>
                        <td><?= $fp['date_facture'] ?></td>
                        <td><?= $fp['nom'] ?></td>
                        <td class="text-end fw-bold"><?= number_format($fp['total_vente'], 0, ',', ' ') ?> F</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white"><h5><i class="fas fa-soap me-2"></i>Détail Service Lavage</h5></div>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead><tr><th>Date</th><th>Immatriculation</th><th>Type</th><th class="text-end">Montant</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $db->query("SELECT * FROM lavage_transactions ORDER BY date_lavage DESC");
                    while($l = $res->fetch()): ?>
                    <tr>
                        <td><?= $l['date_lavage'] ?></td>
                        <td><strong><?= $l['immatriculation'] ?></strong></td>
                        <td><?= $l['type_lavage'] ?></td>
                        <td class="text-end fw-bold"><?= number_format($l['montant'], 0, ',', ' ') ?> F</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
