<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Vérifier et créer la table charges si elle n'existe pas
$db->exec("
CREATE TABLE IF NOT EXISTS charges (
    id_charge INT AUTO_INCREMENT PRIMARY KEY,
    reference_charge VARCHAR(50) UNIQUE,
    id_categorie INT,
    id_fournisseur INT,
    id_bus INT,
    description TEXT,
    montant_ht DECIMAL(12,2),
    montant_tva DECIMAL(12,2) DEFAULT 0,
    montant_ttc DECIMAL(12,2),
    date_charge DATE,
    mode_paiement ENUM('especes', 'wave', 'orange_money', 'virement', 'cheque') DEFAULT 'especes',
    statut_paiement ENUM('paye', 'impaye', 'partiel') DEFAULT 'paye',
    facture_scannee VARCHAR(255),
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_secretaire INT
)
");

// Statistiques des charges par catégorie
$stats_charges = $db->query("
    SELECT 
        cd.nom_categorie,
        COALESCE(SUM(c.montant_ttc), 0) as total,
        COUNT(c.id_charge) as nombre
    FROM categories_depenses cd
    LEFT JOIN charges c ON cd.id_categorie = c.id_categorie 
        AND c.date_charge >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY cd.id_categorie
    ORDER BY total DESC
")->fetchAll();

// Recettes du mois (paiements)
$recettes = $db->query("
    SELECT COALESCE(SUM(montant), 0) as total, COUNT(*) as nombre
    FROM paiements
    WHERE mois_periode >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
    AND statut_paiement = 'paye'
")->fetch(PDO::FETCH_ASSOC);

// Dépenses totales du mois
$depenses = $db->query("
    SELECT COALESCE(SUM(montant_ttc), 0) as total, COUNT(*) as nombre
    FROM charges
    WHERE date_charge >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
")->fetch(PDO::FETCH_ASSOC);

// Détail des dépenses par type
$depenses_detail = $db->query("
    SELECT 
        CASE 
            WHEN cd.nom_categorie = 'Carburant' THEN 'Carburant'
            WHEN cd.nom_categorie IN ('Entretien mécanique', 'Réparations') THEN 'Maintenance'
            WHEN cd.nom_categorie = 'Salaires mécaniciens' THEN 'Salaires'
            WHEN cd.nom_categorie = 'Pièces détachées' THEN 'Pièces'
            WHEN cd.nom_categorie = 'Assurances' THEN 'Assurances'
            ELSE 'Autres'
        END as type_depense,
        COALESCE(SUM(c.montant_ttc), 0) as total,
        ROUND(COALESCE(SUM(c.montant_ttc), 0) / NULLIF((SELECT SUM(montant_ttc) FROM charges WHERE date_charge >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)), 0) * 100, 1) as pourcentage
    FROM charges c
    JOIN categories_depenses cd ON c.id_categorie = cd.id_categorie
    WHERE c.date_charge >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY type_depense
    ORDER BY total DESC
")->fetchAll();

// Tendance des 6 derniers mois
$tendance = $db->query("
    SELECT 
        DATE_FORMAT(date_charge, '%Y-%m') as mois,
        COALESCE(SUM(montant_ttc), 0) as depenses
    FROM charges
    WHERE date_charge >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date_charge, '%Y-%m')
    ORDER BY mois DESC
")->fetchAll();

$resultat_net = ($recettes['total'] ?? 0) - ($depenses['total'] ?? 0);

// Récupérer les dernières charges
$last_charges = $db->query("
    SELECT c.*, cd.nom_categorie, f.nom_fournisseur, b.immatriculation
    FROM charges c
    JOIN categories_depenses cd ON c.id_categorie = cd.id_categorie
    LEFT JOIN fournisseurs f ON c.id_fournisseur = f.id_fournisseur
    LEFT JOIN bus b ON c.id_bus = b.id_bus
    ORDER BY c.date_charge DESC
    LIMIT 15
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>États financiers complets - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { border-radius: 15px; padding: 20px; color: white; margin-bottom: 20px; }
        .stat-recettes { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-depenses { background: linear-gradient(135deg, #dc3545, #fd7e14); }
        .stat-resultat { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        .stat-positive { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-negative { background: linear-gradient(135deg, #dc3545, #fd7e14); }
        .stat-card h2 { font-size: 2rem; font-weight: bold; }
        .progress-bar-custom { transition: width 0.5s; }
    </style>
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> États financiers complets</h2>
            <p>Tableau de bord financier - Période: <?php echo date('F Y'); ?></p>
            <hr>
        </div>
    </div>
    
    <!-- Cartes récapitulatives -->
    <div class="row">
        <div class="col-md-4">
            <div class="stat-card stat-recettes">
                <h4><i class="fas fa-arrow-down"></i> RECETTES</h4>
                <h2><?php echo number_format($recettes['total'], 0, ',', ' '); ?> FCFA</h2>
                <p><?php echo $recettes['nombre']; ?> paiements enregistrés</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-depenses">
                <h4><i class="fas fa-arrow-up"></i> DÉPENSES</h4>
                <h2><?php echo number_format($depenses['total'], 0, ',', ' '); ?> FCFA</h2>
                <p><?php echo $depenses['nombre']; ?> transactions de charges</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card <?php echo $resultat_net >= 0 ? 'stat-positive' : 'stat-negative'; ?>">
                <h4><i class="fas fa-chart-simple"></i> RÉSULTAT NET</h4>
                <h2><?php echo number_format($resultat_net, 0, ',', ' '); ?> FCFA</h2>
                <p><?php echo $resultat_net >= 0 ? 'Bénéfice' : 'Perte'; ?> du mois</p>
            </div>
        </div>
    </div>
    
    <!-- Graphiques -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-chart-pie"></i> Répartition des dépenses par poste</h5>
                </div>
                <div class="card-body">
                    <canvas id="depensesChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-chart-line"></i> Tendance des dépenses (6 mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="tendanceChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Détail des dépenses par catégorie -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-list"></i> Détail des dépenses par catégorie (mois en cours)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Poste de dépense</th><th>Montant (FCFA)</th><th>Pourcentage</th><th>Nb transactions</th></tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_depenses = $depenses['total'];
                            foreach($stats_charges as $stat): 
                                $pourcentage = $total_depenses > 0 ? ($stat['total'] / $total_depenses) * 100 : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($stat['nom_categorie']); ?></strong></td>
                                <td><?php echo number_format($stat['total'], 0, ',', ' '); ?> FCFA</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar progress-bar-custom bg-primary" style="width: <?php echo $pourcentage; ?>%">
                                            <?php echo round($pourcentage, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $stat['nombre']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr><th>TOTAL</th>
                                <th><?php echo number_format($total_depenses, 0, ',', ' '); ?> FCFA</th>
                                <th>100%</th>
                                <th><?php echo $depenses['nombre']; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dernières charges -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-history"></i> Dernières charges enregistrées</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Référence</th><th>Date</th><th>Catégorie</th><th>Description</th><th>Montant</th><th>Fournisseur</th><th>Bus</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($last_charges as $charge): ?>
                                <tr>
                                    <td><code><?php echo $charge['reference_charge'] ?? 'N/A'; ?></code></td>
                                    <td><?php echo date('d/m/Y', strtotime($charge['date_charge'])); ?></td>
                                    <td><?php echo htmlspecialchars($charge['nom_categorie']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($charge['description'] ?? '', 0, 40)); ?>...</td>
                                    <td><strong><?php echo number_format($charge['montant_ttc'], 0, ',', ' '); ?> FCFA</strong></td>
                                    <td><?php echo htmlspecialchars($charge['nom_fournisseur'] ?? '-'); ?></td>
                                    <td><?php echo $charge['immatriculation'] ?? '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($last_charges)): ?>
                                <tr><td colspan="7" class="text-center">Aucune charge enregistrée</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique répartition des dépenses
<?php if(!empty($depenses_detail) && array_sum(array_column($depenses_detail, 'total')) > 0): ?>
const ctx1 = document.getElementById('depensesChart').getContext('2d');
new Chart(ctx1, {
    type: 'pie',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($depenses_detail, 'type_depense')) . "'"; ?>],
        datasets: [{
            data: [<?php echo implode(',', array_column($depenses_detail, 'total')); ?>],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
<?php endif; ?>

// Graphique tendance
<?php if(!empty($tendance)): ?>
const ctx2 = document.getElementById('tendanceChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_reverse(array_column($tendance, 'mois'))) . "'"; ?>],
        datasets: [{
            label: 'Dépenses (FCFA)',
            data: [<?php echo implode(',', array_reverse(array_column($tendance, 'depenses'))); ?>],
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220,53,69,0.1)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
});
<?php endif; ?>
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
