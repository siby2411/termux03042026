<?php
$page_title = "Historique des Coûts (CUMP)";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$db = (new Database())->getConnection();
$id_piece = $_GET['id_piece'] ?? null;

$logs = [];
if($id_piece) {
    $stmt = $db->prepare("SELECT * FROM HISTORIQUE_CUMP WHERE id_piece = ? ORDER BY date_maj ASC");
    $stmt->execute([$id_piece]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <h1><i class="fas fa-history"></i> Historique des variations de prix</h1>
    
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET" class="row g-3">
            <div class="col-auto">
                <label class="form-label">Sélectionner une pièce (ID)</label>
                <input type="number" name="id_piece" class="form-control" value="<?= $id_piece ?>" required>
            </div>
            <div class="col-auto d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Voir l'évolution</button>
            </div>
        </form>
    </div>

    <?php if($logs): ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <canvas id="cumpChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        new Chart(document.getElementById('cumpChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function($l){ return date('d/m/y', strtotime($l['date_maj'])); }, $logs)) ?>,
                datasets: [{
                    label: 'Évolution du CUMP (€)',
                    data: <?= json_encode(array_column($logs, 'nouveau_cump')) ?>,
                    borderColor: '#0d6efd',
                    tension: 0.1,
                    fill: true,
                    backgroundColor: 'rgba(13, 110, 253, 0.1)'
                }]
            }
        });
        </script>
    <?php elseif($id_piece): ?>
        <div class="alert alert-info">Aucun mouvement de stock enregistré pour cette pièce.</div>
    <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
