<?php
// /var/www/piece_auto/public/modules/alertes_stock.php
// Rapport affichant les pièces dont le stock actuel est inférieur au seuil d'alerte minimum.

$page_title = "Alertes de Stock Minimum";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

try {
    // Sélectionne les pièces où le stock actuel est STRICTEMENT inférieur au stock minimum d'alerte
    $query = "
        SELECT
            p.id_piece,
            p.reference,
            p.nom_piece,
            p.stock_actuel,
            p.stock_minimum_alerte,
            f.nom_fournisseur
        FROM PIECES p
        LEFT JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur
        WHERE p.stock_actuel < p.stock_minimum_alerte
        ORDER BY p.stock_actuel ASC
    ";
    
    $stmt = $db->query($query);
    $alertes_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur de base de données lors du chargement des alertes : ' . $e->getMessage() . '</div>';
}

?>

<h1><i class="fas fa-exclamation-circle"></i> <?= $page_title ?></h1>
<p class="lead">Liste des pièces nécessitant un réapprovisionnement urgent.</p>
<hr>

<?= $message ?>

<?php if (empty($alertes_stock)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Félicitations ! Tous les niveaux de stock sont supérieurs ou égaux à leur seuil minimum d'alerte.
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="fas fa-box-open"></i> **<?= count($alertes_stock) ?> pièces** sont en dessous du seuil de réapprovisionnement.
    </div>
    
    <h3>Pièces à commander</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Nom de la Pièce</th>
                    <th class="text-end">Stock Actuel</th>
                    <th class="text-end">Seuil Minimum</th>
                    <th class="text-end">Manque</th>
                    <th>Fournisseur Principal</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alertes_stock as $a): 
                    $manque = $a['stock_minimum_alerte'] - $a['stock_actuel'];
                ?>
                <tr class="table-danger">
                    <td><?= htmlspecialchars($a['reference']) ?></td>
                    <td><?= htmlspecialchars($a['nom_piece']) ?></td>
                    <td class="text-end fw-bold"><?= number_format($a['stock_actuel'], 0, ',', ' ') ?></td>
                    <td class="text-end"><?= number_format($a['stock_minimum_alerte'], 0, ',', ' ') ?></td>
                    <td class="text-end fw-bold text-danger"><?= number_format($manque, 0, ',', ' ') ?></td>
                    <td><?= htmlspecialchars($a['nom_fournisseur'] ?? 'N/A') ?></td>
                    <td class="text-center">
                        <a href="reception_achats.php" class="btn btn-sm btn-primary" title="Passer commande ou enregistrer la réception">
                            <i class="fas fa-dolly-flatbed"></i> Réappro.
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
