<?php
// /var/www/piece_auto/public/modules/forecasting_stock.php
$page_title = "Prévisions & Réapprovisionnement";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Requête complexe pour calculer la consommation moyenne
    // On regarde les ventes sur les 90 derniers jours
    $query = "SELECT 
                p.id_piece, p.reference, p.nom_piece, p.stock_actuel,
                IFNULL(SUM(dv.quantite_vendue), 0) as total_vendue,
                ROUND(IFNULL(SUM(dv.quantite_vendue), 0) / 90, 2) as conso_journaliere
              FROM PIECES p
              LEFT JOIN DETAIL_VENTE dv ON p.id_piece = dv.id_piece
              LEFT JOIN COMMANDE_VENTE cv ON dv.id_commande_vente = cv.id_commande_vente 
                   AND cv.date_commande >= DATE_SUB(NOW(), INTERVAL 90 DAY)
              GROUP BY p.id_piece
              HAVING p.stock_actuel <= 5 OR conso_journaliere > 0
              ORDER BY conso_journaliere DESC, p.stock_actuel ASC";
    
    $stmt = $db->query($query);
    $previsions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur de calcul : ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-crystal-ball text-purple"></i> Prévisions de Stock</h1>
            <p class="text-muted">Analyse basée sur un cycle de 90 jours pour éviter les ruptures.</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-info p-2">Algorithme : Stock Securité + VMJ</span>
        </div>
    </div>

    <?php if (empty($previsions)): ?>
        <div class="alert alert-light border shadow-sm text-center py-5">
            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
            <h4>Tout est sous contrôle !</h4>
            <p>Aucune pièce ne semble atteindre un seuil critique pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($previsions as $p): 
                // Calcul du risque
                $jours_restants = ($p['conso_journaliere'] > 0) ? floor($p['stock_actuel'] / $p['conso_journaliere']) : 999;
                $status_color = ($jours_restants < 7 || $p['stock_actuel'] <= 2) ? 'danger' : 'warning';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 border-top border-4 border-<?= $status_color ?>">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($p['nom_piece']) ?></h5>
                        <h6 class="text-muted mb-3">Réf: <?= htmlspecialchars($p['reference']) ?></h6>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Stock Actuel :</span>
                            <span class="fw-bold"><?= $p['stock_actuel'] ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Vendus (90j) :</span>
                            <span class="fw-bold"><?= $p['total_vendue'] ?></span>
                        </div>
                        <hr>
                        <div class="text-center">
                            <?php if ($jours_restants < 999): ?>
                                <small class="d-block text-muted">Rupture estimée dans :</small>
                                <span class="h4 text-<?= $status_color ?>"><?= $jours_restants ?> Jours</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark">Vitesse de vente faible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="creation_commande_achat.php?piece_id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-<?= $status_color ?> w-100">
                            <i class="fas fa-shopping-cart"></i> Commander en urgence
                        </a>
             </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .text-purple { color: #6f42c1; }
    .border-purple { border-color: #6f42c1; }
</style>

<?php include '../../includes/footer.php'; ?>
