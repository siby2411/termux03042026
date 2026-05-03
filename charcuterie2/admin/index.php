<?php
require_once 'header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$pdo = getPDO();

// Détecter les colonnes disponibles
$columns = $pdo->query("SHOW COLUMNS FROM ventes")->fetchAll(PDO::FETCH_COLUMN);
$montant_col = 'total_ttc';
if (in_array('montant_total', $columns)) $montant_col = 'montant_total';
elseif (in_array('total', $columns)) $montant_col = 'total';
elseif (in_array('prix_total', $columns)) $montant_col = 'prix_total';

// Statistiques
$total_produits = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$total_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_fournisseurs = $pdo->query("SELECT COUNT(*) FROM fournisseurs")->fetchColumn();
$total_ventes = $pdo->query("SELECT COUNT(*) FROM ventes")->fetchColumn();
$ventes_jour = $pdo->query("SELECT COUNT(*) FROM ventes WHERE DATE(date_vente) = CURDATE()")->fetchColumn();

// CA du mois
$ca_mois = $pdo->query("SELECT SUM($montant_col) FROM ventes WHERE MONTH(date_vente) = MONTH(CURDATE())")->fetchColumn();
$ca_mois = $ca_mois ?: 0;

// Alertes stock
$alertes_stock = $pdo->query("SELECT COUNT(*) FROM produits WHERE stock_actuel <= stock_min")->fetchColumn();

// Dernières ventes
$dernieres_ventes = $pdo->query("
    SELECT v.*, c.nom as client_nom, c.prenom as client_prenom 
    FROM ventes v 
    LEFT JOIN clients c ON v.client_id = c.id 
    ORDER BY v.date_vente DESC LIMIT 5
")->fetchAll();
?>

<div class="row">
    <div class="col">
        <h2><i class="fas fa-chart-line"></i> Tableau de bord</h2>
        <p>Bienvenue <?= escape($_SESSION['admin_nom']) ?> - <?= date('d/m/Y H:i') ?></p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Produits</h5>
                <h2><?= $total_produits ?></h2>
                <small>En catalogue</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Ventes</h5>
                <h2><?= $total_ventes ?></h2>
                <small>Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Clients</h5>
                <h2><?= $total_clients ?></h2>
                <small>Enregistrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Fournisseurs</h5>
                <h2><?= $total_fournisseurs ?></h2>
                <small>Partenaires</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5>Ventes aujourd'hui</h5>
                <h2><?= $ventes_jour ?></h2>
                <small>Nouvelles ventes</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5>Alertes stock</h5>
                <h2><?= $alertes_stock ?></h2>
                <small>Produits en rupture</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Chiffre d'affaires</h5>
                <h2><?= formatMoney($ca_mois) ?></h2>
                <small>Ce mois-ci</small>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Actions rapides</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="produits.php?action=ajouter" class="btn btn-outline-primary w-100">Ajouter un produit</a>
            </div>
            <div class="col-md-3">
                <a href="ventes.php?action=nouvelle" class="btn btn-outline-success w-100">Nouvelle vente</a>
            </div>
            <div class="col-md-3">
                <a href="stock.php" class="btn btn-outline-warning w-100">Gérer le stock</a>
            </div>
            <div class="col-md-3">
                <a href="rapports.php" class="btn btn-outline-info w-100">Voir les rapports</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($dernieres_ventes)): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5>Dernières ventes</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                 '<th>N°</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th> '
            </thead>
            <tbody>
                <?php foreach ($dernieres_ventes as $v): ?>
                <tr>
                    <td><?= escape($v['numero_vente'] ?? $v['id']) ?></td>
                    <td><?= escape($v['client_nom'] . ' ' . ($v['client_prenom'] ?? '')) ?></td>
                    <td><?= formatDate($v['date_vente']) ?></td>
                    <td class="fw-bold"><?= formatMoney($v[$montant_col] ?? 0) ?></td>
                    <td><span class="badge bg-success">Terminée</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
