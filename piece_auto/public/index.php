<?php
// /var/www/piece_auto/public/index.php
$page_title = "Tableau de Bord";
require_once __DIR__ . '/../config/Database.php';
include '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Stats de base (CA, Achats, Alertes)
    $ca = $db->query("SELECT SUM(total_commande) FROM COMMANDE_VENTE")->fetchColumn() ?: 0;
    $achats_attente = $db->query("SELECT COUNT(*) FROM COMMANDES_ACHAT WHERE statut = 'En attente'")->fetchColumn() ?: 0;
    $stock_alerte = $db->query("SELECT COUNT(*) FROM PIECES WHERE stock_actuel <= 5")->fetchColumn() ?: 0;

    // 2. DONNÉES POUR LE GRAPHIQUE (7 derniers jours)
    // On crée une série de dates pour s'assurer d'avoir des 0 si aucune vente n'est faite
    $labels = [];
    $data_ventes = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d/m', strtotime($date));
        
        $stmt = $db->prepare("SELECT SUM(total_commande) FROM COMMANDE_VENTE WHERE DATE(date_commande) = :d");
        $stmt->execute([':d' => $date]);
        $val = $stmt->fetchColumn() ?: 0;
        $data_ventes[] = $val;
    }

    // 3. Dernières ventes
    $recentes = $db->query("SELECT cv.*, c.nom FROM COMMANDE_VENTE cv JOIN CLIENTS c ON cv.id_client = c.id_client ORDER BY date_commande DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
}
?>

<div class="container-fluid">
    <h1 class="h2 mb-4"><i class="fas fa-chart-line text-primary"></i> Pilotage de l'Activité</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white p-3">
                <small class="text-uppercase opacity-75">Chiffre d'Affaires</small>
                <h2 class="fw-bold mb-0"><?= number_format($ca, 2, ',', ' ') ?> €</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning text-dark p-3">
                <small class="text-uppercase opacity-75">Commandes Fournisseurs</small>
                <h2 class="fw-bold mb-0"><?= $achats_attente ?> <small class="h6">en attente</small></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white p-3">
                <small class="text-uppercase opacity-75">Alertes Stock</small>
                <h2 class="fw-bold mb-0"><?= $stock_alerte ?> <small class="h6">réf. critiques</small></h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Évolution des Ventes (7 jours)</div>
                <div class="card-body">
                    <canvas id="salesChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">Raccourcis</div>
                <div class="card-body">
                    <a href="modules/creer_commande_vente.php" class="btn btn-primary w-100 mb-2"><i class="fas fa-plus"></i> Nouvelle Vente</a>
                    <a href="modules/creation_commande_achat.php" class="btn btn-outline-dark w-100 mb-2"><i class="fas fa-truck"></i> Commander Pièces</a>
                    <a href="modules/gestion_pieces.php" class="btn btn-outline-secondary w-100"><i class="fas fa-search"></i> Inventaire</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Ventes (€)',
            data: <?= json_encode($data_ventes) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.3,
            pointRadius: 5
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: value => value + ' €' } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
