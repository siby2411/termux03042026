<?php
include 'includes/db.php';
include 'includes/header.php';

// Calcul du Chiffre d'Affaires (Total des commandes facturées)
$ca_query = $pdo->query("SELECT SUM(total_ht) as total FROM commandes WHERE etat = 'facturee'");
$ca = $ca_query->fetch()['total'] ?? 0;

// Liste des commandes à traiter par la compta (validées ou facturées)
$factures = $pdo->query("SELECT * FROM commandes WHERE etat IN ('validee', 'facturee') ORDER BY date_commande DESC")->fetchAll();
?>

<h1 class="h2 border-bottom pb-2 text-primary"><i class="fas fa-calculator"></i> Service Comptabilité</h1>

<div class="row mb-4 mt-3">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Chiffre d'Affaires Réalisé</div>
            <div class="card-body">
                <h3 class="card-title"><?= number_format($ca, 2, ',', ' ') ?> €</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-dark bg-warning mb-3">
            <div class="card-header">Factures en attente</div>
            <div class="card-body">
                <h3 class="card-title"><?= count($factures) ?> Dossiers</h3>
            </div>
        </div>
    </div>
</div>

<h3 class="mt-4">Journal des Ventes</h3>
<table class="table table-hover">
    <thead>
        <tr>
            <th>Date</th>
            <th>Client</th>
            <th>Montant HT</th>
            <th>TVA (20%)</th>
            <th>Total TTC</th>
            <th>État</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($factures as $f): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($f['date_commande'])) ?></td>
            <td><?= htmlspecialchars($f['client_nom']) ?></td>
            <td><?= number_format($f['total_ht'], 2) ?> €</td>
            <td><?= number_format($f['total_ht'] * 0.20, 2) ?> €</td>
            <td><strong><?= number_format($f['total_ht'] * 1.20, 2) ?> €</strong></td>
            <td><span class="badge bg-secondary"><?= $f['etat'] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
