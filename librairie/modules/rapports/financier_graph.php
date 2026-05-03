<?php
require_once '../../includes/config.php';

if (!isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Graphiques analytiques';
$periode = $_GET['periode'] ?? 'mois';
$caissier_id = $_GET['caissier_id'] ?? '';

switch($periode) {
    case 'jour':
        $date_debut = date('Y-m-d');
        $date_fin = date('Y-m-d');
        break;
    case 'semaine':
        $date_debut = date('Y-m-d', strtotime('monday this week'));
        $date_fin = date('Y-m-d');
        break;
    default:
        $date_debut = date('Y-m-01');
        $date_fin = date('Y-m-d');
}

$where = "v.date_vente BETWEEN ? AND ? AND v.statut = 'validee'";
$params = [$date_debut . ' 00:00:00', $date_fin . ' 23:59:59'];

if ($caissier_id) {
    $where .= " AND v.utilisateur_id = ?";
    $params[] = $caissier_id;
}

// Ventes par jour
$stmt = $pdo->prepare("
    SELECT DATE(v.date_vente) as date, SUM(v.montant_total) as total, COUNT(*) as nb_ventes
    FROM ventes v
    WHERE $where
    GROUP BY DATE(v.date_vente)
    ORDER BY date
");
$stmt->execute($params);
$ventes_jour = $stmt->fetchAll();

// Récupérer les caissiers
$caissiers = $pdo->query("SELECT id, nom, prenom FROM utilisateurs WHERE role IN ('caissier', 'admin')")->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-chart-line"></i> Tableau de bord analytique</h4>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Période</label>
                        <select name="periode" class="form-control" onchange="this.form.submit()">
                            <option value="jour" <?php echo $periode == 'jour' ? 'selected' : ''; ?>>Aujourd'hui</option>
                            <option value="semaine" <?php echo $periode == 'semaine' ? 'selected' : ''; ?>>Cette semaine</option>
                            <option value="mois" <?php echo $periode == 'mois' ? 'selected' : ''; ?>>Ce mois</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Caissier</label>
                        <select name="caissier_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Tous</option>
                            <?php foreach($caissiers as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $caissier_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo $c['prenom'] . ' ' . $c['nom']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-success form-control" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </form>
                
                <canvas id="ventesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ventesData = <?php echo json_encode($ventes_jour); ?>;
const labels = ventesData.map(item => item.date);
const caData = ventesData.map(item => item.total);
const nbData = ventesData.map(item => item.nb_ventes);

new Chart(document.getElementById('ventesChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Chiffre d\'affaires (FCFA)',
            data: caData,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Nombre de ventes',
            data: nbData,
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { title: { display: true, text: 'CA (FCFA)' } },
            y1: { position: 'right', title: { display: true, text: 'Nombre de ventes' } }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
