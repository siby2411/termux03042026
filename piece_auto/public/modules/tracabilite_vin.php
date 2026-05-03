<?php
$page_title = "Traçabilité par VIN";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';
$db = (new Database())->getConnection();

$vin = $_GET['vin'] ?? '';
$historique = [];

if($vin) {
    $query = "SELECT cv.date_commande, p.nom_piece, p.reference 
              FROM DETAIL_VENTE dv
              JOIN COMMANDE_VENTE cv ON dv.id_commande_vente = cv.id_commande_vente
              JOIN PIECES p ON dv.id_piece = p.id_piece
              WHERE cv.vin_vehicule = :vin"; // Suppose l'existence d'une colonne vin_vehicule
    $stmt = $db->prepare($query);
    $stmt->execute([':vin' => $vin]);
    $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="container">
    <div class="card p-4 shadow-sm">
        <h4>Recherche d'Historique par Châssis (VIN)</h4>
        <form class="d-flex mb-4">
            <input type="text" name="vin" class="form-control me-2" placeholder="Entrez le VIN..." value="<?= $vin ?>">
            <button class="btn btn-primary">Rechercher</button>
        </form>

        <?php if($historique): ?>
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Référence</th><th>Pièce installée</th></tr></thead>
                <tbody>
                    <?php foreach($historique as $h): ?>
                        <tr><td><?= $h['date_commande'] ?></td><td><?= $h['reference'] ?></td><td><?= $h['nom_piece'] ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif($vin): ?>
            <p class="text-muted">Aucun historique trouvé pour ce VIN.</p>
        <?php endif; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
