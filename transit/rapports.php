<?php
require_once 'config.php';
require_once 'header.php';

// Récupération des données financières
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Chiffre d'affaires par mois
$stmt = $pdo->prepare("SELECT MONTH(date_emission) as mois, SUM(montant_ttc) as ca 
                       FROM factures 
                       WHERE YEAR(date_emission) = ? 
                       GROUP BY MONTH(date_emission)");
$stmt->execute([$year]);
$ca_mensuel = $stmt->fetchAll();

// Top 10 clients
$stmt = $pdo->query("SELECT c.raison_sociale, SUM(f.montant_ttc) as total_achats 
                     FROM factures f 
                     JOIN clients c ON f.client_id = c.id 
                     GROUP BY c.id 
                     ORDER BY total_achats DESC LIMIT 10");
$top_clients = $stmt->fetchAll();

// Statistiques globales
$stmt = $pdo->query("SELECT 
                     SUM(montant_ttc) as ca_total,
                     AVG(montant_ttc) as panier_moyen,
                     COUNT(*) as nb_factures
                     FROM factures 
                     WHERE YEAR(date_emission) = $year");
$stats_globales = $stmt->fetch();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>États Financiers & Rapports</h1>
            <div>
                <select class="form-select" id="anneeSelect" onchange="changerAnnee()">
                    <option value="2024" <?php echo $year == '2024' ? 'selected' : ''; ?>>2024</option>
                    <option value="2023" <?php echo $year == '2023' ? 'selected' : ''; ?>>2023</option>
                    <option value="2022" <?php echo $year == '2022' ? 'selected' : ''; ?>>2022</option>
                </select>
            </div>
        </div>
        
        <!-- Cartes KPI -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo number_format($stats_globales['ca_total'] ?? 0, 0, ',', ' '); ?> FCFA</h3>
                    <p>Chiffre d'Affaires Total</p>
                    <small><?php echo $year; ?></small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3><?php echo number_format($stats_globales['panier_moyen'] ?? 0, 0, ',', ' '); ?> FCFA</h3>
                    <p>Panier Moyen</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h3><?php echo $stats_globales['nb_factures'] ?? 0; ?></h3>
                    <p>Nombre de Factures</p>
                </div>
            </div>
        </div>
        
        <!-- Graphique CA Mensuel -->
        <div class="card-modern p-4 mb-4">
            <h5>Évolution du Chiffre d'Affaires <?php echo $year; ?></h5>
            <canvas id="caChart" height="100"></canvas>
        </div>
        
        <div class="row">
            <!-- Top Clients -->
            <div class="col-md-6">
                <div class="card-modern p-4 mb-4">
                    <h5>Top 10 Clients</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Client</th><th>Montant Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_clients as $client): ?>
                                <tr>
                                    <td><?php echo $client['raison_sociale']; ?></td>
                                    <td><?php echo number_format($client['total_achats'], 0, ',', ' '); ?> FCFA</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Distribution par type de transport -->
            <div class="col-md-6">
                <div class="card-modern p-4 mb-4">
                    <h5>Répartition par Transport</h5>
                    <canvas id="transportRepartition" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- États financiers détaillés -->
        <div class="card-modern p-4">
            <h5>État des Créances</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Facture N°</th>
                            <th>Montant TTC</th>
                            <th>Date Échéance</th>
                            <th>Jours de retard</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT f.*, c.raison_sociale 
                                            FROM factures f 
                                            JOIN clients c ON f.client_id = c.id 
                                            WHERE f.statut = 'en_attente' 
                                            ORDER BY f.date_echeance ASC");
                        while($facture = $stmt->fetch()):
                            $retard = (strtotime(date('Y-m-d')) - strtotime($facture['date_echeance'])) / 86400;
                        ?>
                        <tr>
                            <td><?php echo $facture['raison_sociale']; ?></td>
                            <td><?php echo $facture['numero_facture']; ?></td>
                            <td><?php echo number_format($facture['montant_ttc'], 0, ',', ' '); ?> FCFA</td>
                            <td><?php echo $facture['date_echeance']; ?></td>
                            <td>
                                <?php if($retard > 0): ?>
                                    <span class="text-danger"><?php echo $retard; ?> jours</span>
                                <?php else: ?>
                                    <span class="text-success">À jour</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-warning">En attente</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique CA
const ctx = document.getElementById('caChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'CA Mensuel (Millions FCFA)',
            data: <?php 
                $ca_data = array_fill(0, 12, 0);
                foreach($ca_mensuel as $ca) {
                    $ca_data[$ca['mois']-1] = $ca['ca'] / 1000000;
                }
                echo json_encode($ca_data);
            ?>,
            borderColor: '#004080',
            backgroundColor: 'rgba(0,64,128,0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.raw.toFixed(2) + ' Millions FCFA';
                    }
                }
            }
        }
    }
});

// Graphique transport
const ctx2 = document.getElementById('transportRepartition').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Maritime', 'Aéroportuaire'],
        datasets: [{
            data: [70, 30],
            backgroundColor: ['#004080', '#ffd700']
        }]
    }
});

function changerAnnee() {
    let annee = document.getElementById('anneeSelect').value;
    window.location.href = 'rapports.php?year=' + annee;
}
</script>

<?php require_once 'footer.php'; ?>
