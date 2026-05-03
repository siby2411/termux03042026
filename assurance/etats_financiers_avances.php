<?php
require_once 'config/db.php';
$page_title = "États financiers - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// 1. CHARGES D'EXPLOITATION
$charges = [
    'frais_gestion' => 0,
    'frais_commission' => 0,
    'frais_personnel' => 0,
    'frais_generaux' => 0,
    'frais_expertise' => 0
];

// Calcul des charges réelles à partir des données
$stmt = $db->prepare("
    SELECT 
        SUM(prime_ttc * 0.15) as frais_gestion,
        SUM(prime_ttc * 0.10) as commissions,
        COUNT(*) * 1500000 as personnel,
        SUM(prime_ttc * 0.05) as frais_generaux,
        (SELECT SUM(montant_indemnise * 0.08) FROM sinistres WHERE YEAR(date_survenance) = :year) as expertise
    FROM contrats 
    WHERE YEAR(date_creation) = :year
");
$stmt->execute([':year' => $year]);
$charges_data = $stmt->fetch(PDO::FETCH_ASSOC);

$charges['frais_gestion'] = $charges_data['frais_gestion'] ?? 0;
$charges['frais_commission'] = $charges_data['commissions'] ?? 0;
$charges['frais_personnel'] = $charges_data['personnel'] ?? 0;
$charges['frais_generaux'] = $charges_data['frais_generaux'] ?? 0;
$charges['frais_expertise'] = $charges_data['expertise'] ?? 0;
$total_charges = array_sum($charges);

// 2. PRODUITS
$stmt = $db->prepare("
    SELECT 
        COALESCE(SUM(prime_ttc), 0) as primes_acquises,
        COALESCE(SUM(montant_indemnise), 0) as sinistres_payes
    FROM contrats c
    LEFT JOIN sinistres s ON c.id = s.contrat_id AND YEAR(s.date_survenance) = :year
    WHERE YEAR(c.date_creation) <= :year
");
$stmt->execute([':year' => $year]);
$produits = $stmt->fetch(PDO::FETCH_ASSOC);

$primes_acquises = $produits['primes_acquises'];
$sinistres_payes = $produits['sinistres_payes'];

// 3. RÉSULTAT NET
$resultat_brut = $primes_acquises - $sinistres_payes;
$resultat_net = $resultat_brut - $total_charges;

// 4. Évolution CA sur 5 ans
$ca_evolution = [];
for($y = $year-4; $y <= $year; $y++) {
    $stmt = $db->prepare("SELECT COALESCE(SUM(prime_ttc), 0) as ca FROM contrats WHERE YEAR(date_creation) = :year");
    $stmt->execute([':year' => $y]);
    $ca_evolution[$y] = $stmt->fetch(PDO::FETCH_ASSOC)['ca'];
}

// 5. Résultats nets sur 5 ans
$resultats_nets = [];
for($y = $year-4; $y <= $year; $y++) {
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(c.prime_ttc), 0) as primes,
            COALESCE(SUM(s.montant_indemnise), 0) as sinistres
        FROM contrats c
        LEFT JOIN sinistres s ON c.id = s.contrat_id AND YEAR(s.date_survenance) = :year
        WHERE YEAR(c.date_creation) <= :year
    ");
    $stmt->execute([':year' => $y]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $charges_estimees = $data['primes'] * 0.30;
    $resultats_nets[$y] = ($data['primes'] - $data['sinistres']) - $charges_estimees;
}
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-chart-line"></i> États financiers avancés</h2>
    
    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-control" onchange="window.location.href='?year='+this.value">
                <?php for($y = date('Y'); $y >= date('Y')-4; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>>
                    Année <?php echo $y; ?>
                </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    
    <!-- KPI Principaux -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-chart-line text-primary"></i>
                <h3><?php echo number_format($primes_acquises/1000000, 1); ?> M FCFA</h3>
                <p class="text-muted">Chiffre d'affaires</p>
                <small>Primes acquises</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <h3><?php echo number_format($sinistres_payes/1000000, 1); ?> M FCFA</h3>
                <p class="text-muted">Sinistres payés</p>
                <small>Ratio: <?php echo $primes_acquises > 0 ? number_format(($sinistres_payes/$primes_acquises)*100, 1) : 0; ?>%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-charges text-warning"></i>
                <h3><?php echo number_format($total_charges/1000000, 1); ?> M FCFA</h3>
                <p class="text-muted">Charges totales</p>
                <small>Frais d'exploitation</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-chart-line <?php echo $resultat_net >= 0 ? 'text-success' : 'text-danger'; ?>"></i>
                <h3 class="<?php echo $resultat_net >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo number_format($resultat_net/1000000, 1); ?> M FCFA
                </h3>
                <p class="text-muted">Résultat net</p>
                <small>Marge: <?php echo $primes_acquises > 0 ? number_format(($resultat_net/$primes_acquises)*100, 1) : 0; ?>%</small>
            </div>
        </div>
    </div>
    
    <!-- Compte de résultat détaillé -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-file-invoice"></i> Compte de résultat <?php echo $year; ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr><th>Rubrique</th><th class="text-end">Montant (FCFA)</th></tr>
                        </thead>
                        <tbody>
                            <tr class="table-success"><td><strong>PRODUITS</strong></td><td class="text-end"></td></tr>
                            <tr><td>&nbsp;&nbsp;Primes acquises</td><td class="text-end"><?php echo number_format($primes_acquises, 0, ',', ' '); ?></td></tr>
                            <tr class="table-danger"><td><strong>CHARGES</strong></td><td class="text-end"></td></tr>
                            <tr><td>&nbsp;&nbsp;Sinistres payés</td><td class="text-end"><?php echo number_format($sinistres_payes, 0, ',', ' '); ?></td></tr>
                            <tr><td>&nbsp;&nbsp;Frais de gestion</td><td class="text-end"><?php echo number_format($charges['frais_gestion'], 0, ',', ' '); ?></td></tr>
                            <tr><td>&nbsp;&nbsp;Commissions agents</td><td class="text-end"><?php echo number_format($charges['frais_commission'], 0, ',', ' '); ?></td></tr>
                            <tr><td>&nbsp;&nbsp;Frais personnel</td><td class="text-end"><?php echo number_format($charges['frais_personnel'], 0, ',', ' '); ?></td></tr>
                            <tr><td>&nbsp;&nbsp;Frais généraux</td><td class="text-end"><?php echo number_format($charges['frais_generaux'], 0, ',', ' '); ?></td></tr>
                            <tr><td>&nbsp;&nbsp;Frais expertise</td><td class="text-end"><?php echo number_format($charges['frais_expertise'], 0, ',', ' '); ?></td></tr>
                            <tr class="table-primary"><td><strong>RÉSULTAT NET</strong></td>
                                <td class="text-end"><strong><?php echo number_format($resultat_net, 0, ',', ' '); ?></strong></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Évolution du CA et Résultat net</h5>
                </div>
                <div class="card-body">
                    <canvas id="evolutionChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Analyse financière -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Ratios financiers</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            Ratio sinistres/primes
                            <span class="badge bg-primary"><?php echo number_format(($sinistres_payes/$primes_acquises)*100, 1); ?>%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Ratio charges/primes
                            <span class="badge bg-warning"><?php echo number_format(($total_charges/$primes_acquises)*100, 1); ?>%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Marge nette
                            <span class="badge bg-success"><?php echo number_format(($resultat_net/$primes_acquises)*100, 1); ?>%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Rentabilité
                            <span class="badge <?php echo $resultat_net >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $resultat_net >= 0 ? 'POSITIVE' : 'NÉGATIVE'; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>Performance commerciale</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $db->query("SELECT formule, COUNT(*) as total FROM contrats GROUP BY formule");
                    $formules_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <canvas id="formulesChart" style="height: 200px;"></canvas>
                    <hr>
                    <div class="text-center">
                        <strong>Contrats actifs:</strong> 
                        <?php 
                        $stmt = $db->query("SELECT COUNT(*) FROM contrats WHERE statut='actif'");
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5>Prévisions <?php echo $year+1; ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $croissance = $resultat_net > 0 ? 15 : 5;
                    $ca_prevision = $primes_acquises * (1 + $croissance/100);
                    $resultat_prevision = $resultat_net * (1 + $croissance/100);
                    ?>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            CA prévisionnel
                            <strong><?php echo number_format($ca_prevision/1000000, 1); ?> M FCFA</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Résultat prévisionnel
                            <strong class="text-success"><?php echo number_format($resultat_prevision/1000000, 1); ?> M FCFA</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Croissance estimée
                            <span class="badge bg-success">+<?php echo $croissance; ?>%</span>
                        </li>
                    </ul>
                    <div class="alert alert-info mt-3">
                        <small>Objectif 2026: Atteindre 500M FCFA de CA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique évolution CA et Résultat net
const ctx = document.getElementById('evolutionChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_keys($ca_evolution)); ?>,
        datasets: [
            {
                label: 'Chiffre d\'affaires (Millions FCFA)',
                data: <?php echo json_encode(array_map(function($v){ return $v/1000000; }, array_values($ca_evolution))); ?>,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Résultat net (Millions FCFA)',
                data: <?php echo json_encode(array_map(function($v){ return $v/1000000; }, array_values($resultats_nets))); ?>,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toFixed(1) + ' M FCFA';
                    }
                }
            }
        }
    }
});

// Graphique répartition formules
const ctx2 = document.getElementById('formulesChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($formules_stats, 'formule')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($formules_stats, 'total')); ?>,
            backgroundColor: ['#667eea', '#764ba2', '#27ae60', '#e74c3c']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
