<?php
require_once 'config.php';
require_once 'header.php';

// Statistiques pour le dashboard
$stats = [];

// Total déclarations
$stmt = $pdo->query("SELECT COUNT(*) as total, 
                     SUM(CASE WHEN statut = 'acquitte' THEN 1 ELSE 0 END) as acquittees 
                     FROM declarations_douane");
$stats['declarations'] = $stmt->fetch();

// Chiffre d'affaires du mois
$stmt = $pdo->query("SELECT SUM(montant_ttc) as ca FROM factures 
                     WHERE MONTH(date_emission) = MONTH(CURRENT_DATE()) 
                     AND YEAR(date_emission) = YEAR(CURRENT_DATE())");
$stats['ca_mois'] = $stmt->fetch()['ca'] ?? 0;

// Clients actifs
$stmt = $pdo->query("SELECT COUNT(*) as total FROM clients");
$stats['clients'] = $stmt->fetch()['total'];

// Déclarations par type de transport
$stmt = $pdo->query("SELECT type_transport, COUNT(*) as total 
                     FROM declarations_douane GROUP BY type_transport");
$transport_stats = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="display-6">Tableau de Bord</h1>
        <p class="text-muted">Bienvenue sur OMEGA TRANSIT - Solution de gestion intégrée</p>
    </div>
</div>

<!-- Cartes statistiques -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <h3><?php echo number_format($stats['declarations']['total'], 0, ',', ' '); ?></h3>
            <p class="mb-0">Déclarations totales</p>
            <small><?php echo $stats['declarations']['acquittees']; ?> acquittées</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <h3><?php echo number_format($stats['ca_mois'], 0, ',', ' '); ?> FCFA</h3>
            <p class="mb-0">CA du mois</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3><?php echo $stats['clients']; ?></h3>
            <p class="mb-0">Clients actifs</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <h3>24h</h3>
            <p class="mb-0">Délai moyen dédouanement</p>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row">
    <div class="col-md-6">
        <div class="card-modern p-4 mb-4">
            <h5>Évolution des déclarations (2024)</h5>
            <canvas id="evolutionChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-modern p-4 mb-4">
            <h5>Répartition par mode de transport</h5>
            <canvas id="transportChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Dernières déclarations -->
<div class="card-modern p-4">
    <h5>Dernières déclarations en douane</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>N° Déclaration</th>
                    <th>Client</th>
                    <th>Transport</th>
                    <th>Date dépôt</th>
                    <th>Valeur CIF</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT d.*, c.raison_sociale 
                                    FROM declarations_douane d 
                                    JOIN clients c ON d.client_id = c.id 
                                    ORDER BY d.date_depot DESC LIMIT 5");
                while($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo $row['numero_declaration']; ?></td>
                    <td><?php echo $row['raison_sociale']; ?></td>
                    <td><?php echo ucfirst($row['type_transport']); ?></td>
                    <td><?php echo $row['date_depot']; ?></td>
                    <td><?php echo number_format($row['valeur_cif'], 0, ',', ' '); ?> FCFA</td>
                    <td>
                        <span class="badge bg-<?php 
                            echo $row['statut'] == 'acquitte' ? 'success' : 
                                ($row['statut'] == 'depose' ? 'info' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($row['statut']); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Graphique évolution
const ctx = document.getElementById('evolutionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
        datasets: [{
            label: 'Déclarations',
            data: [12, 19, 15, 17, 14, 23],
            borderColor: '#004080',
            backgroundColor: 'rgba(0,64,128,0.1)',
            tension: 0.4
        }]
    }
});

// Graphique transport
const ctx2 = document.getElementById('transportChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Maritime', 'Aéroportuaire'],
        datasets: [{
            data: [65, 35],
            backgroundColor: ['#004080', '#ffd700']
        }]
    }
});
</script>

<?php require_once 'footer.php'; ?>
