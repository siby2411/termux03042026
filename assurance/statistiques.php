<?php
require_once 'config/db.php';
$page_title = "Statistiques - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();

// Statistiques globales
$stats = [];

// Contrats par formule
$formules = $db->query("SELECT formule, COUNT(*) as total FROM contrats GROUP BY formule")->fetchAll();

// Sinistres par type
$sinistres_type = $db->query("SELECT type_sinistre, COUNT(*) as total FROM sinistres GROUP BY type_sinistre")->fetchAll();

// Paiements par mois
$paiements_mois = $db->query("SELECT DATE_FORMAT(date_paiement, '%Y-%m') as mois, SUM(montant) as total 
                              FROM paiements WHERE YEAR(date_paiement) = YEAR(CURDATE())
                              GROUP BY mois ORDER BY mois")->fetchAll();

// Top clients
$top_clients = $db->query("SELECT cl.nom, cl.prenom, cl.raison_sociale, cl.type_client, SUM(c.prime_ttc) as total_primes
                           FROM contrats c JOIN clients cl ON c.client_id = cl.id
                           GROUP BY cl.id ORDER BY total_primes DESC LIMIT 5")->fetchAll();
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Statistiques</h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Contrats par formule</h5>
                </div>
                <div class="card-body">
                    <canvas id="formulesChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Sinistres par type</h5>
                </div>
                <div class="card-body">
                    <canvas id="sinistresChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Évolution des paiements (<?php echo date('Y'); ?>)</h5>
                </div>
                <div class="card-body">
                    <canvas id="paiementsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Top 5 clients (par primes)</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr><th>Client</th><th>Total primes (FCFA)</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_clients as $c): ?>
                            <tr>
                                <td><?php echo $c['type_client']=='particulier' ? $c['prenom'].' '.$c['nom'] : $c['raison_sociale']; ?></td>
                                <td><?php echo number_format($c['total_primes'], 0, ',', ' '); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Graphique formules
new Chart(document.getElementById('formulesChart'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($formules, 'formule')); ?>,
        datasets: [{data: <?php echo json_encode(array_column($formules, 'total')); ?>, backgroundColor: ['#667eea', '#764ba2', '#27ae60', '#e74c3c']}]
    }
});

// Graphique sinistres
new Chart(document.getElementById('sinistresChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($sinistres_type, 'type_sinistre')); ?>,
        datasets: [{label: 'Nombre de sinistres', data: <?php echo json_encode(array_column($sinistres_type, 'total')); ?>, backgroundColor: '#764ba2'}]
    }
});

// Graphique paiements
new Chart(document.getElementById('paiementsChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($paiements_mois, 'mois')); ?>,
        datasets: [{label: 'Paiements (FCFA)', data: <?php echo json_encode(array_column($paiements_mois, 'total')); ?>, borderColor: '#667eea', tension: 0.4}]
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
