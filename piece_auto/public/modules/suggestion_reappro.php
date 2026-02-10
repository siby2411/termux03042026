<?php
$page_title = "Suggestions de Réapprovisionnement";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$pieces_a_reappro = []; // Initialisation pour éviter le Warning

try {
    $query = "SELECT p.id_piece, p.reference, p.nom_piece, p.stock_actuel, p.stock_minimum_alerte 
              FROM PIECES p 
              WHERE p.stock_actuel < p.stock_minimum_alerte";
    $stmt = $db->query($query);
    $pieces_a_reappro = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pieces_a_reappro as &$p) {
        $p['quantite_suggeree'] = $p['stock_minimum_alerte'] * 2 - $p['stock_actuel'];
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
}
?>

<h1><i class="fas fa-magic"></i> Suggestions</h1>
<table class="table">
    <thead>
        <tr><th>Réf</th><th>Pièce</th><th>Stock</th><th>Besoin</th></tr>
    </thead>
    <tbody>
        <?php foreach ($pieces_a_reappro as $p): ?>
        <tr>
            <td><?= $p['reference'] ?></td>
            <td><?= $p['nom_piece'] ?></td>
            <td><?= $p['stock_actuel'] ?></td>
            <td class="text-primary fw-bold"><?= $p['quantite_suggeree'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
