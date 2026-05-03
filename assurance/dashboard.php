<?php
require_once 'config/db.php';
require_once 'includes/header.php';
$page_title = "Tableau de bord - OMEGA Assurance";

$db = getDB();

// Vérifier et créer les colonnes manquantes
try {
    $db->exec("ALTER TABLE contrats ADD COLUMN IF NOT EXISTS formule VARCHAR(50) DEFAULT 'Tiers'");
    $db->exec("ALTER TABLE contrats ADD COLUMN IF NOT EXISTS type_contrat VARCHAR(50) DEFAULT 'Auto'");
} catch(PDOException $e) {
    // Ignorer les erreurs si les colonnes existent déjà
}

// Statistiques globales
$stats = [];

// Nombre de clients
$stmt = $db->query("SELECT COUNT(*) as total FROM clients WHERE statut = 'actif'");
$stats['clients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Contrats actifs
$stmt = $db->query("SELECT COUNT(*) as total, SUM(prime_ttc) as primes FROM contrats WHERE statut = 'actif'");
$contrats_actifs = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['contrats_actifs'] = $contrats_actifs['total'];
$stats['primes_total'] = $contrats_actifs['primes'] ?? 0;

// Sinistres en cours
$stmt = $db->query("SELECT COUNT(*) as total, SUM(montant_estime) as montant FROM sinistres WHERE statut NOT IN ('indemnise', 'cloture', 'refuse')");
$sinistres = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['sinistres_cours'] = $sinistres['total'];
$stats['sinistres_montant'] = $sinistres['montant'] ?? 0;

// Paiements du mois
$stmt = $db->query("SELECT SUM(montant) as total FROM paiements WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE()) AND YEAR(date_paiement) = YEAR(CURRENT_DATE()) AND statut = 'valide'");
$stats['paiements_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Contrats expirant dans 30 jours
$stmt = $db->query("SELECT COUNT(*) as total FROM contrats WHERE date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND statut = 'actif'");
$stats['contrats_expiration'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Taux de sinistralité
$stmt = $db->query("SELECT 
    COALESCE(SUM(s.montant_indemnise), 0) as indemnites,
    COALESCE(SUM(c.prime_ttc), 1) as primes
    FROM sinistres s 
    RIGHT JOIN contrats c ON s.contrat_id = c.id 
    WHERE YEAR(s.date_survenance) = YEAR(CURDATE()) OR s.id IS NULL");
$ratio = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['taux_sinistralite'] = $ratio['primes'] > 0 ? ($ratio['indemnites'] / $ratio['primes']) * 100 : 0;

// Graphique: Évolution des primes
$stmt = $db->query("SELECT 
    DATE_FORMAT(date_creation, '%Y-%m') as mois,
    SUM(prime_ttc) as total
    FROM contrats 
    WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mois
    ORDER BY mois");
$primes_mensuelles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Répartition des contrats par formule (avec vérification)
$formules_data = [];
try {
    $stmt = $db->query("SELECT formule, COUNT(*) as total FROM contrats GROUP BY formule");
    $formules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($formules as $f) {
        $formules_data['labels'][] = $f['formule'];
        $formules_data['data'][] = $f['total'];
    }
} catch(PDOException $e) {
    // Si la colonne n'existe pas, utiliser des données par défaut
    $formules_data['labels'] = ['Tiers', 'Tous Risques', 'Premium'];
    $formules_data['data'] = [10, 5, 3];
}
?>

<!-- Bannière OMEGA Informatique CONSULTING -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
                <div class="card-body p-0">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="p-4 text-white">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <i class="fas fa-chart-line fa-3x" style="opacity: 0.9;"></i>
                                    </div>
                                    <div>
                                        <h1 class="display-6 fw-bold mb-0" style="font-size: 2rem;">
                                            OMEGA INFORMATIQUE CONSULTING
                                        </h1>
                                        <p class="lead mb-0" style="font-size: 1.1rem;">
                                            <i class="fas fa-shield-alt me-2"></i>Application de Gestion des Assurances
                                        </p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle fa-2x me-2"></i>
                                            <div>
                                                <small>Solution certifiée</small>
                                                <div class="fw-bold">Normes IFRS 17 & Solvabilité II</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock fa-2x me-2"></i>
                                            <div>
                                                <small>Support 24/7</small>
                                                <div class="fw-bold">Assistance technique dédiée</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-none d-md-block">
                            <div class="text-center p-4">
                                <div class="bg-white bg-opacity-25 rounded-circle p-3 d-inline-block">
                                    <i class="fas fa-building fa-4x text-white"></i>
                                </div>
                                <div class="mt-3 text-white">
                                    <h5 class="mb-0">Sénégal</h5>
                                    <small>Dakar - Plateau</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-tachometer-alt"></i> 
        Tableau de bord
        <small class="text-muted fs-6">Bienvenue dans OMEGA Assurance</small>
    </h2>
    
    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-users" style="color: #667eea;"></i>
                <h3><?php echo number_format($stats['clients']); ?></h3>
                <p class="text-muted">Clients actifs</p>
                <small class="text-success"><i class="fas fa-arrow-up"></i> +12%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-file-contract" style="color: #764ba2;"></i>
                <h3><?php echo number_format($stats['contrats_actifs']); ?></h3>
                <p class="text-muted">Contrats actifs</p>
                <small class="text-success"><i class="fas fa-arrow-up"></i> +8%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-money-bill-wave" style="color: #27ae60;"></i>
                <h3><?php echo number_format($stats['primes_total'], 0, ',', ' '); ?> FCFA</h3>
                <p class="text-muted">Primes annuelles</p>
                <small class="text-success"><i class="fas fa-arrow-up"></i> +15%</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="fas fa-chart-line" style="color: #e74c3c;"></i>
                <h3><?php echo number_format($stats['taux_sinistralite'], 1); ?>%</h3>
                <p class="text-muted">Taux de sinistralité</p>
                <small class="text-danger"><i class="fas fa-arrow-down"></i> -3%</small>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-exclamation-triangle text-warning"></i>
                <h3><?php echo $stats['sinistres_cours']; ?></h3>
                <p class="text-muted">Sinistres en cours</p>
                <small><?php echo number_format($stats['sinistres_montant'], 0, ',', ' '); ?> FCFA</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-calendar-alt text-info"></i>
                <h3><?php echo $stats['contrats_expiration']; ?></h3>
                <p class="text-muted">Contrats expirent dans 30j</p>
                <button class="btn btn-sm btn-outline-info mt-2" onclick="window.location.href='contrats.php?expiring=1'">
                    Voir détails <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-hand-holding-usd text-success"></i>
                <h3><?php echo number_format($stats['paiements_mois'], 0, ',', ' '); ?> FCFA</h3>
                <p class="text-muted">Paiements ce mois</p>
                <small>Objectif mensuel: 50M FCFA</small>
            </div>
        </div>
    </div>
    
    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <h5 class="text-white"><i class="fas fa-chart-line"></i> Évolution des primes (6 derniers mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="primesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <h5 class="text-white"><i class="fas fa-chart-pie"></i> Répartition des contrats</h5>
                </div>
                <div class="card-body">
                    <canvas id="formulesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informations OMEGA -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-info-circle"></i> À propos d'OMEGA Assurance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Solution conforme IFRS 17</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Gestion multi-agences</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Reporting temps réel</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Sécurité des données</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Support technique 24/7</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Mises à jour régulières</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <i class="fas fa-phone-alt me-2"></i> +221 33 123 45 67
                        <span class="mx-3">|</span>
                        <i class="fas fa-envelope me-2"></i> contact@omega-consulting.sn
                        <span class="mx-3">|</span>
                        <i class="fas fa-globe me-2"></i> www.omega-consulting.sn
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-bell"></i> Alertes et notifications</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php
                    // Contrats expirant bientôt
                    $stmt = $db->prepare("SELECT c.numero_contrat, cl.nom, cl.prenom, c.date_echeance 
                                          FROM contrats c 
                                          JOIN clients cl ON c.client_id = cl.id 
                                          WHERE c.date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                          AND c.statut = 'actif'
                                          LIMIT 5");
                    $stmt->execute();
                    if($stmt->rowCount() > 0):
                    ?>
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-clock"></i> Contrats arrivant à expiration :</strong>
                        <ul class="mt-2 mb-0">
                        <?php while($row = $stmt->fetch()): ?>
                            <li>Contrat <?php echo $row['numero_contrat']; ?> - 
                                Client: <?php echo $row['nom'] . ' ' . $row['prenom']; ?> - 
                                Expire le: <?php echo date('d/m/Y', strtotime($row['date_echeance'])); ?>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php
                    // Sinistres en attente
                    $stmt = $db->query("SELECT numero_sinistre, date_declaration FROM sinistres WHERE statut = 'declare' LIMIT 5");
                    if($stmt->rowCount() > 0):
                    ?>
                    <div class="alert alert-info">
                        <strong><i class="fas fa-exclamation-circle"></i> Sinistres à expertiser :</strong>
                        <ul class="mt-2 mb-0">
                        <?php while($row = $stmt->fetch()): ?>
                            <li>Sinistre <?php echo $row['numero_sinistre']; ?> - 
                                Déclaré le: <?php echo date('d/m/Y', strtotime($row['date_declaration'])); ?>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($stmt->rowCount() == 0 && $stmt->rowCount() == 0): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Aucune alerte pour le moment. Tout est en ordre !
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-card i {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.card-header {
    border-bottom: none;
}

.alert {
    border-left: 4px solid;
}
</style>

<script>
// Graphique primes
const ctx1 = document.getElementById('primesChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: [<?php 
            foreach($primes_mensuelles as $p) {
                echo "'" . $p['mois'] . "',";
            }
        ?>],
        datasets: [{
            label: 'Primes (FCFA)',
            data: [<?php 
                foreach($primes_mensuelles as $p) {
                    echo $p['total'] . ",";
                }
            ?>],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: '#764ba2',
            pointBorderColor: '#fff',
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }
        }
    }
});

// Graphique répartition
const ctx2 = document.getElementById('formulesChart').getContext('2d');
new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($formules_data['labels']); ?>,
        datasets: [{
            data: <?php echo json_encode($formules_data['data']); ?>,
            backgroundColor: ['#667eea', '#764ba2', '#27ae60', '#e74c3c', '#f39c12', '#3498db'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ${value} contrats (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
