<?php
$page_title = "Analyse de Fiabilité";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$db = (new Database())->getConnection();

// Analyse des motifs de retour/remplacement (Simulé par volume de vente vs rappels)
$query_pannes = "SELECT p.nom_piece, COUNT(dv.id_piece) as frequence_remplacement
                 FROM DETAIL_VENTE dv
                 JOIN PIECES p ON dv.id_piece = p.id_piece
                 GROUP BY p.id_piece ORDER BY frequence_remplacement DESC LIMIT 10";
$pannes = $db->query($query_pannes)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card shadow-sm p-4">
    <h3><i class="fas fa-microscope"></i> Top 10 des pièces à fort taux de remplacement</h3>
    <p>Ces données permettent d'identifier les vulnérabilités par modèle de véhicule.</p>
    <canvas id="pannesChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('pannesChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($pannes, 'nom_piece')) ?>,
        datasets: [{
            label: 'Nombre de remplacements',
            data: <?= json_encode(array_column($pannes, 'frequence_remplacement')) ?>,
            backgroundColor: '#ff6384'
        }]
    }
});
</script>
<?php include '../../includes/footer.php'; ?>
