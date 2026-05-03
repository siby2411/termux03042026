<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Tableau de bord';

// Statistiques globales
$stats = [];
$stats['total_livres'] = $pdo->query("SELECT COUNT(*) FROM livres")->fetchColumn();
$stats['total_clients'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$stats['ventes_jour'] = $pdo->query("SELECT COUNT(*) FROM ventes WHERE DATE(date_vente) = CURDATE()")->fetchColumn();
$stats['chiffre_jour'] = $pdo->query("SELECT SUM(montant_total) FROM ventes WHERE DATE(date_vente) = CURDATE() AND statut = 'validee'")->fetchColumn();
$stats['stock_faible'] = $pdo->query("SELECT COUNT(*) FROM livres WHERE quantite_stock <= quantite_min")->fetchColumn();
$stats['meilleur_vente'] = $pdo->query("
    SELECT l.titre, SUM(vl.quantite) as total_vendu 
    FROM ventes_lignes vl 
    JOIN livres l ON vl.livre_id = l.id 
    GROUP BY l.id 
    ORDER BY total_vendu DESC 
    LIMIT 1
")->fetch();

// Ventes récentes
$ventes_recentes = $pdo->query("
    SELECT v.*, u.username as caissier 
    FROM ventes v 
    JOIN utilisateurs u ON v.utilisateur_id = u.id 
    ORDER BY v.date_vente DESC 
    LIMIT 10
")->fetchAll();

// Alertes stock
$alertes_stock = $pdo->query("
    SELECT * FROM livres 
    WHERE quantite_stock <= quantite_min 
    ORDER BY quantite_stock ASC 
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-chart-line"></i> 
            <strong>Bienvenue <?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> - 
            Tableau de bord de la librairie OMEGA JUMTOU SAKOU KHAM KHAM TECH
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Livres</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_livres']); ?></h2>
                    </div>
                    <i class="fas fa-book fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Clients</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_clients']); ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Ventes Aujourd'hui</h6>
                        <h2 class="mb-0"><?php echo $stats['ventes_jour']; ?></h2>
                        <small>CA: <?php echo number_format($stats['chiffre_jour'] ?? 0, 0, ',', ' '); ?> FCFA</small>
                    </div>
                    <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Stock Faible</h6>
                        <h2 class="mb-0"><?php echo $stats['stock_faible']; ?></h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Dernières ventes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Facture</th>
                                <th>Date</th>
                                <th>Caissier</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </thead>
                            <tbody>
                                <?php foreach($ventes_recentes as $vente): ?>
                                <tr>
                                    <td><?php echo $vente['numero_facture']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                                    <td><?php echo $vente['caissier']; ?></td>
                                    <td><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                    <td>
                                        <span class="badge bg-<?php echo $vente['statut'] == 'validee' ? 'success' : 'danger'; ?>">
                                            <?php echo $vente['statut']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-triangle"></i> Alertes stock</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach($alertes_stock as $alerte): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($alerte['titre']); ?></strong>
                                    <br>
                                    <small>Auteur: <?php echo htmlspecialchars($alerte['auteur']); ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger">Stock: <?php echo $alerte['quantite_stock']; ?></span>
                                    <br>
                                    <small>Min: <?php echo $alerte['quantite_min']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($alertes_stock)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Aucune alerte de stock
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
