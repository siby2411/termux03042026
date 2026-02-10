<?php
// /var/www/piece_auto/public/modules/historique_mouvements_stock.php
$page_title = "Historique des Mouvements de Stock";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Requête alignée sur les nouvelles colonnes
    $query = "SELECT m.*, p.reference, p.nom_piece 
              FROM MOUVEMENTS_STOCK m
              JOIN PIECES p ON m.id_piece = p.id_piece
              ORDER BY m.date_mouvement DESC";
    $stmt = $db->query($query);
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
}
?>

<h1><i class="fas fa-history"></i> Historique des Mouvements</h1>
<p class="text-muted">Suivi détaillé des entrées et sorties (Achat, Vente, Ajustement).</p>

<table class="table table-sm table-striped">
    <thead class="table-dark">
        <tr>
            <th>Date</th>
            <th>Pièce</th>
            <th>Type</th>
            <th class="text-end">Impact</th>
            <th class="text-end">Avant</th>
            <th class="text-end">Après</th>
            <th class="text-end">Prix U.</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($mouvements as $m): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?></td>
            <td><strong><?= htmlspecialchars($m['reference']) ?></strong><br><small><?= htmlspecialchars($m['nom_piece']) ?></small></td>
            <td>
                <span class="badge <?= (strpos($m['type_mouvement'], 'Vente') !== false) ? 'bg-warning' : 'bg-success' ?>">
                    <?= htmlspecialchars($m['type_mouvement']) ?>
                </span>
            </td>
            <td class="text-end fw-bold"><?= $m['quantite_impact'] ?></td>
            <td class="text-end text-muted"><?= $m['stock_avant_mouvement'] ?></td>
            <td class="text-end fw-bold"><?= $m['stock_apres_mouvement'] ?></td>
            <td class="text-end"><?= number_format($m['prix_unitaire'], 2) ?> €</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
