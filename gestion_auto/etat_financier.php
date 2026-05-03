<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance();
$conn = $db->getConnection();

// Période par défaut (mois en cours)
$debut = date('Y-m-01');
$fin = date('Y-m-t');

if(isset($_POST['periode'])) {
    $debut = $_POST['debut'];
    $fin = $_POST['fin'];
}

// Chiffre d'affaires total
$query_ca = $conn->prepare("
    SELECT 
        COALESCE(SUM(prix_vente), 0) as ca_ventes,
        COALESCE(SUM(frais_dossier), 0) as frais_ventes,
        COALESCE(SUM(commission), 0) as commissions_ventes
    FROM ventes 
    WHERE date_vente BETWEEN ? AND ? AND statut = 'finalise'
");
$query_ca->execute([$debut, $fin]);
$ca_data = $query_ca->fetch();

// CA Locations
$query_ca_locations = $conn->prepare("
    SELECT 
        COALESCE(SUM(DATEDIFF(date_fin, date_debut) * prix_jour), 0) as ca_locations,
        COALESCE(SUM(caution), 0) as cautions
    FROM locations 
    WHERE date_debut BETWEEN ? AND ? AND statut IN ('encours', 'termine')
");
$query_ca_locations->execute([$debut, $fin]);
$ca_locations_data = $query_ca_locations->fetch();

// Statistiques générales
$total_ca = $ca_data['ca_ventes'] + $ca_locations_data['ca_locations'] + $ca_data['frais_ventes'] + $ca_data['commissions_ventes'];

// Ventes par mois (6 derniers mois)
$query_evolution = $conn->query("
    SELECT 
        DATE_FORMAT(date_vente, '%Y-%m') as mois,
        SUM(prix_vente) as ca_ventes,
        COUNT(*) as nb_ventes
    FROM ventes 
    WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND statut = 'finalise'
    GROUP BY DATE_FORMAT(date_vente, '%Y-%m')
    ORDER BY mois
");
$evolution_data = $query_evolution->fetchAll();

// Top véhicules vendus
$query_top_ventes = $conn->prepare("
    SELECT 
        mar.nom as marque_nom,
        m.nom as modele_nom,
        COUNT(v.id) as nb_ventes,
        SUM(ve.prix_vente) as ca_total
    FROM ventes v
    JOIN vehicules ve ON v.vehicule_id = ve.id
    JOIN modeles m ON ve.modele_id = m.id
    JOIN marques mar ON m.marque_id = mar.id
    WHERE v.date_vente BETWEEN ? AND ? AND v.statut = 'finalise'
    GROUP BY ve.modele_id, mar.nom, m.nom
    ORDER BY ca_total DESC
    LIMIT 5
");
$query_top_ventes->execute([$debut, $fin]);
$top_ventes = $query_top_ventes->fetchAll();

// Locations actives
$query_locations_actives = $conn->prepare("
    SELECT 
        l.*,
        c.nom, c.prenom,
        ve.immatriculation,
        mar.nom as marque_nom,
        m.nom as modele_nom,
        DATEDIFF(l.date_fin, l.date_debut) as duree,
        (DATEDIFF(l.date_fin, l.date_debut) * l.prix_jour) as montant
    FROM locations l
    JOIN clients c ON l.client_id = c.id
    JOIN vehicules ve ON l.vehicule_id = ve.id
    JOIN modeles m ON ve.modele_id = m.id
    JOIN marques mar ON m.marque_id = mar.id
    WHERE l.statut = 'encours'
    ORDER BY l.date_debut
");
$query_locations_actives->execute();
$locations_actives = $query_locations_actives->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-graph-up text-success me-2"></i>État Financier
            </h1>
            <button class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimer
            </button>
        </div>
        <p class="text-muted">Analyse financière de votre activité</p>
    </div>
</div>

<!-- Filtres Période -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Date de début</label>
                <input type="date" name="debut" class="form-control" value="<?= $debut ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de fin</label>
                <input type="date" name="fin" class="form-control" value="<?= $fin ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="periode" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Appliquer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques Principales -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($total_ca, 0, ',', ' ') ?> €</h4>
                        <p class="card-text">Chiffre d'Affaires Total</p>
                    </div>
                    <i class="bi bi-currency-euro fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($ca_data['ca_ventes'], 0, ',', ' ') ?> €</h4>
                        <p class="card-text">CA Ventes</p>
                    </div>
                    <i class="bi bi-cart-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($ca_locations_data['ca_locations'], 0, ',', ' ') ?> €</h4>
                        <p class="card-text">CA Locations</p>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($ca_data['frais_ventes'] + $ca_data['commissions_ventes'], 0, ',', ' ') ?> €</h4>
                        <p class="card-text">Frais & Commissions</p>
                    </div>
                    <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique Évolution -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Évolution du Chiffre d'Affaires</h5>
            </div>
            <div class="card-body">
                <canvas id="evolutionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Ventes -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Top 5 des Ventes</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach($top_ventes as $index => $vente): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-2">#<?= $index + 1 ?></span>
                            <?= $vente['marque_nom'] ?> <?= $vente['modele_nom'] ?>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success"><?= number_format($vente['ca_total'], 0, ',', ' ') ?> €</div>
                            <small class="text-muted"><?= $vente['nb_ventes'] ?> vente(s)</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($top_ventes)): ?>
                    <div class="list-group-item text-center text-muted">
                        Aucune vente sur cette période
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Locations Actives -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Locations en Cours</h5>
                <span class="badge bg-warning"><?= count($locations_actives) ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Véhicule</th>
                                <th>Période</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($locations_actives as $location): ?>
                            <tr>
                                <td>
                                    <small><?= $location['prenom'] ?> <?= $location['nom'] ?></small>
                                </td>
                                <td>
                                    <small><?= $location['marque_nom'] ?> <?= $location['modele_nom'] ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m', strtotime($location['date_debut'])) ?> - 
                                        <?= date('d/m', strtotime($location['date_fin'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-success"><?= number_format($location['montant'], 0, ',', ' ') ?> €</strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($locations_actives)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Aucune location en cours
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition CA -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Répartition du Chiffre d'Affaires</h5>
            </div>
            <div class="card-body">
                <canvas id="repartitionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Données pour le graphique d'évolution
const evolutionData = {
    labels: [<?php 
        foreach($evolution_data as $data) {
            echo "'" . date('m/Y', strtotime($data['mois'] . '-01')) . "',";
        }
    ?>],
    datasets: [{
        label: 'Chiffre d\'Affaires Ventes (€)',
        data: [<?php 
            foreach($evolution_data as $data) {
                echo $data['ca_ventes'] . ',';
            }
        ?>],
        borderColor: '#27ae60',
        backgroundColor: 'rgba(39, 174, 96, 0.1)',
        fill: true,
        tension: 0.4
    }]
};

// Données pour la répartition
const repartitionData = {
    labels: ['Ventes', 'Locations', 'Frais & Commissions'],
    datasets: [{
        data: [
            <?= $ca_data['ca_ventes'] ?>,
            <?= $ca_locations_data['ca_locations'] ?>,
            <?= $ca_data['frais_ventes'] + $ca_data['commissions_ventes'] ?>
        ],
        backgroundColor: ['#27ae60', '#f39c12', '#3498db']
    }]
};

// Initialisation des graphiques
document.addEventListener('DOMContentLoaded', function() {
    // Graphique d'évolution
    const ctxEvolution = document.getElementById('evolutionChart')?.getContext('2d');
    if (ctxEvolution) {
        new Chart(ctxEvolution, {
            type: 'line',
            data: evolutionData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution sur 6 mois'
                    }
                }
            }
        });
    }

    // Graphique de répartition
    const ctxRepartition = document.getElementById('repartitionChart')?.getContext('2d');
    if (ctxRepartition) {
        new Chart(ctxRepartition, {
            type: 'doughnut',
            data: repartitionData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
