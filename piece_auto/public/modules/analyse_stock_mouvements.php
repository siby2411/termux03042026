<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$db = (new Database())->getConnection();
$query = "SELECT MS.*, P.reference_sku, P.nom_piece FROM MOUVEMENTS_STOCK MS JOIN PIECES P ON MS.id_piece = P.id_piece ORDER BY MS.date_mouvement DESC LIMIT 100";
$mouvements = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
    <h2><i class="fas fa-history"></i> Historique Stock</h2>
    <table class="table table-bordered shadow-sm">
        <thead class="table-dark"><tr><th>Date</th><th>Référence</th><th>Quantité</th><th>Type</th></tr></thead>
        <tbody>
            <?php foreach($mouvements as $m): ?>
            <tr>
                <td><?= $m['date_mouvement'] ?></td>
                <td><?= $m['reference_sku'] ?></td>
                <td class="<?= $m['quantite_impact'] > 0 ? 'text-success':'text-danger'?>"><?= $m['quantite_impact'] ?></td>
                <td><span class="badge bg-info"><?= $m['type_mouvement'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
