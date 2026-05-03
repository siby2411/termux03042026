<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$page_title = "IA Prédictive : Forecasting Stock";
include '../../includes/header.php';

// Algorithme : (Ventes 30 derniers jours / 30) * 30 jours + 20% de stock de sécurité
$query = "SELECT 
            p.id_piece, 
            p.reference, 
            p.nom_piece, 
            p.stock_actuel, 
            p.cump,
            COALESCE(SUM(lv.quantite), 0) as total_vendu_30j,
            f.nom_fournisseur
          FROM PIECES p
          LEFT JOIN LIGNE_VENTE lv ON p.id_piece = lv.id_piece 
            AND lv.id_commande_vente IN (SELECT id_commande_vente FROM COMMANDE_VENTE WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 30 DAY))
          LEFT JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur
          GROUP BY p.id_piece
          ORDER BY total_vendu_30j DESC";
$stmt = $db->query($query);
?>

<div class="alert alert-info shadow-sm border-0 mb-4">
    <i class="fas fa-brain me-2"></i> 
    <strong>Analyse IA :</strong> Les prévisions sont basées sur la vitesse de rotation des 30 derniers jours avec un coefficient de sécurité de 1.2x.
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Article</th>
                    <th class="text-center">Vendus (30j)</th>
                    <th class="text-center">Stock Actuel</th>
                    <th class="text-center">Prévision (Mois +1)</th>
                    <th class="text-center">Conseil d'Achat</th>
                    <th class="text-end">Budget Estimé</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    // Calcul de la prévision : Ventes + 20% de sécurité
                    $prevision_besoin = ceil($row['total_vendu_30j'] * 1.2);
                    
                    // Calcul de ce qu'il faut commander
                    $a_commander = max(0, $prevision_besoin - $row['stock_actuel']);
                    $budget = $a_commander * $row['cump'];
                    
                    $status_class = ($a_commander > 0) ? 'text-danger fw-bold' : 'text-success';
                ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?= $row['nom_piece'] ?></div>
                        <small class="text-muted"><?= $row['reference'] ?> | <?= $row['nom_fournisseur'] ?></small>
                    </td>
                    <td class="text-center"><?= $row['total_vendu_30j'] ?></td>
                    <td class="text-center"><?= $row['stock_actuel'] ?></td>
                    <td class="text-center text-primary fw-bold"><?= $prevision_besoin ?></td>
                    <td class="text-center <?= $status_class ?>">
                        <?php if($a_commander > 0): ?>
                            <i class="fas fa-arrow-up"></i> Commander <?= $a_commander ?> pces
                        <?php else: ?>
                            <i class="fas fa-check-circle"></i> Stock Suffisant
                        <?php endif; ?>
                    </td>
                    <td class="text-end fw-bold">
                        <?= number_format($budget, 0, ',', ' ') ?> F
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded border">
    <h6><i class="fas fa-lightbulb text-warning me-2"></i> Note Stratégique</h6>
    <p class="small text-muted mb-0">
        Si un article a une prévision élevée mais un stock faible, il est prioritaire pour éviter une perte de Chiffre d'Affaires le mois prochain. 
        Le budget total estimé pour le réapprovisionnement optimal est calculé sur la base de votre <b>CUMP</b> actuel.
    </p>
</div>

<?php include '../../includes/footer.php'; ?>
