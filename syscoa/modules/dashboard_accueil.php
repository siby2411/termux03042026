<?php
// modules/dashboard_accueil.php
$id_exercice = $_SESSION['id_exercice'];

// Récupérer les statistiques pour le dashboard
function getDashboardStats($pdo, $id_exercice) {
    $stats = [];
    
    // Écritures du mois
    $sql_ecritures = "SELECT COUNT(*) as total, 
                      SUM(debit) as total_debit, 
                      SUM(credit) as total_credit
                      FROM ecritures 
                      WHERE id_exercice = :id_exercice 
                      AND MONTH(date_ecriture) = MONTH(NOW())";
    $stmt = $pdo->prepare($sql_ecritures);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $stats['ecritures_mois'] = $stmt->fetch();
    
    // Solde bancaire
    $sql_banque = "SELECT SUM(debit - credit) as solde 
                   FROM ecritures 
                   WHERE id_exercice = :id_exercice 
                   AND compte_num LIKE '52%'";
    $stmt = $pdo->prepare($sql_banque);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $stats['solde_banque'] = $stmt->fetchColumn();
    
    // Articles en stock
    $sql_stock = "SELECT COUNT(*) as total, 
                  SUM(stock_actuel * prix_unitaire) as valeur
                  FROM articles_stock 
                  WHERE actif = 1";
    $stats['stock'] = $pdo->query($sql_stock)->fetch();
    
    // Tiers actifs
    $stats['total_tiers'] = $pdo->query("SELECT COUNT(*) FROM tiers WHERE actif = 1")->fetchColumn();
    
    // Rapprochements en attente
    $sql_rapprochement = "SELECT COUNT(*) as pending 
                          FROM releves_bancaires r
                          WHERE r.id_exercice = :id_exercice
                          AND ABS(r.solde_releve - (
                              SELECT SUM(debit - credit) 
                              FROM ecritures e 
                              WHERE e.compte_num = r.compte_num
                              AND e.date_ecriture <= r.date_releve
                          )) > 0.01";
    $stmt = $pdo->prepare($sql_rapprochement);
    $stmt->execute([':id_exercice' => $id_exercice]);
    $stats['rapprochements_pending'] = $stmt->fetchColumn();
    
    return $stats;
}

$stats = getDashboardStats($pdo, $id_exercice);

// Dernières écritures
$sql_last_ecritures = "SELECT e.*, j.libelle as journal_libelle 
                       FROM ecritures e
                       LEFT JOIN journaux j ON e.journal_code = j.code
                       WHERE e.id_exercice = :id_exercice
                       ORDER BY e.date_ecriture DESC, e.id DESC
                       LIMIT 10";
$stmt = $pdo->prepare($sql_last_ecritures);
$stmt->execute([':id_exercice' => $id_exercice]);
$last_ecritures = $stmt->fetchAll();

// Alertes
$alerts = [];
if ($stats['rapprochements_pending'] > 0) {
    $alerts[] = [
        'type' => 'warning',
        'message' => $stats['rapprochements_pending'] . ' rapprochement(s) bancaire(s) en attente',
        'icon' => 'fas fa-university'
    ];
}

// Stocks faibles
$sql_stock_faible = "SELECT COUNT(*) as faible 
                     FROM articles_stock 
                     WHERE stock_actuel < stock_min 
                     AND actif = 1";
$stock_faible = $pdo->query($sql_stock_faible)->fetchColumn();
if ($stock_faible > 0) {
    $alerts[] = [
        'type' => 'danger',
        'message' => $stock_faible . ' article(s) en stock minimum',
        'icon' => 'fas fa-boxes'
    ];
}
?>

<!-- Dashboard Accueil -->
<div class="dashboard-accueil">
    <!-- Alertes rapides -->
    <?php if (!empty($alerts)): ?>
    <div class="alert-container">
        <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?>">
            <i class="<?php echo $alert['icon']; ?> alert-icon"></i>
            <div><?php echo $alert['message']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Stats Principales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['ecritures_mois']['total'], 0, ',', ' '); ?></div>
                <div class="stat-label">Écritures ce mois</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <?php echo number_format($stats['ecritures_mois']['total_debit'] - $stats['ecritures_mois']['total_credit'], 0, ',', ' '); ?> FCFA
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-university"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['solde_banque'], 0, ',', ' '); ?> F</div>
                <div class="stat-label">Solde bancaire</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +5% vs mois dernier
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['stock']['total'], 0, ',', ' '); ?></div>
                <div class="stat-label">Articles en stock</div>
                <div class="stat-change">
                    Valeur : <?php echo number_format($stats['stock']['valeur'], 0, ',', ' '); ?> FCFA
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($stats['total_tiers'], 0, ',', ' '); ?></div>
                <div class="stat-label">Tiers actifs</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +3 ce mois
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques et données -->
    <div class="dashboard-grid">
        <!-- Graphique Évolution CA -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Évolution du Chiffre d'Affaires
                </h3>
                <div class="card-actions">
                    <select class="form-control form-control-sm" style="width: auto;">
                        <option>12 derniers mois</option>
                        <option>6 derniers mois</option>
                        <option>Année en cours</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartCA"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Dernières écritures -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Dernières écritures
                </h3>
                <a href="?module=comptabilite" class="btn btn-sm btn-outline">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>Compte</th>
                                <th>Libellé</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($last_ecritures as $ecriture): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($ecriture['date_ecriture'])); ?></td>
                                <td><span class="badge badge-primary"><?php echo $ecriture['journal_code']; ?></span></td>
                                <td><code><?php echo $ecriture['compte_num']; ?></code></td>
                                <td><?php echo htmlspecialchars(substr($ecriture['libelle'], 0, 50)); ?></td>
                                <td class="text-end">
                                    <?php if ($ecriture['debit'] > 0): ?>
                                    <span class="text-success"><?php echo number_format($ecriture['debit'], 0, ',', ' '); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($ecriture['credit'] > 0): ?>
                                    <span class="text-danger"><?php echo number_format($ecriture['credit'], 0, ',', ' '); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- État des rapprochements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-university"></i>
                    État des rapprochements
                </h3>
                <a href="?module=rapprochement" class="btn btn-sm btn-primary">Rapprocher</a>
            </div>
            <div class="card-body">
                <div class="rapprochement-status">
                    <?php 
                    $sql_comptes = "SELECT c.numero, c.libelle, 
                                   (SELECT SUM(debit - credit) FROM ecritures 
                                    WHERE compte_num = c.numero AND id_exercice = :id_exercice) as solde_comptable
                                   FROM comptes_ohada c 
                                   WHERE c.numero LIKE '52%' AND c.actif = 1";
                    $stmt = $pdo->prepare($sql_comptes);
                    $stmt->execute([':id_exercice' => $id_exercice]);
                    $comptes = $stmt->fetchAll();
                    
                    foreach ($comptes as $compte):
                        $sql_releve = "SELECT solde_releve FROM releves_bancaires 
                                      WHERE compte_num = :compte_num 
                                      AND id_exercice = :id_exercice 
                                      ORDER BY date_releve DESC LIMIT 1";
                        $stmt = $pdo->prepare($sql_releve);
                        $stmt->execute([':compte_num' => $compte['numero'], ':id_exercice' => $id_exercice]);
                        $releve = $stmt->fetch();
                    ?>
                    <div class="compte-item">
                        <div class="compte-info">
                            <div class="compte-numero"><?php echo $compte['numero']; ?></div>
                            <div class="compte-libelle"><?php echo $compte['libelle']; ?></div>
                        </div>
                        <div class="compte-soldes">
                            <div class="solde-comptable">
                                <span class="label">Comptable :</span>
                                <span class="value"><?php echo number_format($compte['solde_comptable'], 0, ',', ' '); ?> F</span>
                            </div>
                            <div class="solde-releve">
                                <span class="label">Relevé :</span>
                                <span class="value">
                                    <?php if ($releve): ?>
                                    <?php echo number_format($releve['solde_releve'], 0, ',', ' '); ?> F
                                    <?php else: ?>
                                    <span class="text-muted">Non rapproché</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="compte-action">
                            <?php if ($releve): 
                                $difference = abs($releve['solde_releve'] - $compte['solde_comptable']);
                                if ($difference < 0.01):
                            ?>
                            <span class="badge badge-success">OK</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Diff: <?php echo number_format($difference, 0, ',', ' '); ?> F</span>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge badge-danger">À faire</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Progression clôture -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-times"></i>
                    Progression de la clôture
                </h3>
                <span class="badge badge-warning"><?php echo $stats['rapprochements_pending']; ?> en attente</span>
            </div>
            <div class="card-body">
                <?php 
                $sql_etapes = "SELECT COUNT(*) as total, 
                              SUM(CASE WHEN statut = 'TERMINE' THEN 1 ELSE 0 END) as terminees
                              FROM calendrier_cloture 
                              WHERE id_exercice = :id_exercice";
                $stmt = $pdo->prepare($sql_etapes);
                $stmt->execute([':id_exercice' => $id_exercice]);
                $etapes = $stmt->fetch();
                
                $pourcentage = $etapes['total'] > 0 ? ($etapes['terminees'] / $etapes['total'] * 100) : 0;
                ?>
                <div class="cloture-progress">
                    <div class="progress-info">
                        <span><?php echo $etapes['terminees']; ?> / <?php echo $etapes['total']; ?> étapes</span>
                        <span><?php echo round($pourcentage, 1); ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo $pourcentage; ?>%"></div>
                    </div>
                    
                    <div class="etapes-list">
                        <?php 
                        $sql_details = "SELECT * FROM calendrier_cloture 
                                       WHERE id_exercice = :id_exercice 
                                       ORDER BY ordre_etape";
                        $stmt = $pdo->prepare($sql_details);
                        $stmt->execute([':id_exercice' => $id_exercice]);
                        $details = $stmt->fetchAll();
                        
                        foreach ($details as $etape):
                        ?>
                        <div class="etape-item">
                            <div class="etape-check">
                                <?php if ($etape['statut'] == 'TERMINE'): ?>
                                <i class="fas fa-check-circle text-success"></i>
                                <?php elseif ($etape['statut'] == 'EN_COURS'): ?>
                                <i class="fas fa-spinner fa-spin text-warning"></i>
                                <?php else: ?>
                                <i class="fas fa-clock text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div class="etape-info">
                                <div class="etape-nom"><?php echo $etape['nom_etape']; ?></div>
                                <?php if ($etape['date_execution']): ?>
                                <div class="etape-date"><?php echo date('d/m/Y', strtotime($etape['date_execution'])); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="etape-statut">
                                <span class="badge badge-<?php echo strtolower($etape['statut']) == 'termine' ? 'success' : ($etape['statut'] == 'EN_COURS' ? 'warning' : 'secondary'); ?>">
                                    <?php echo $etape['statut']; ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt"></i>
                Actions rapides
            </h3>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <button class="quick-action" onclick="window.location.href='?module=comptabilite'">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nouvelle écriture</span>
                </button>
                <button class="quick-action" onclick="window.location.href='?module=rapprochement'">
                    <i class="fas fa-sync-alt"></i>
                    <span>Rapprocher banque</span>
                </button>
                <button class="quick-action" onclick="window.location.href='?module=articles'">
                    <i class="fas fa-box"></i>
                    <span>Entrée stock</span>
                </button>
                <button class="quick-action" onclick="window.location.href='?module=soldes'">
                    <i class="fas fa-chart-pie"></i>
                    <span>Voir SIG</span>
                </button>
                <button class="quick-action" onclick="window.open('impression.php', '_blank')">
                    <i class="fas fa-print"></i>
                    <span>Imprimer rapport</span>
                </button>
                <button class="quick-action" onclick="window.location.href='?module=cloture'">
                    <i class="fas fa-lock"></i>
                    <span>Clôture partielle</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique Chiffre d'Affaires
const ctxCA = document.getElementById('chartCA').getContext('2d');
const chartCA = new Chart(ctxCA, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Chiffre d\'Affaires (FCFA)',
            data: [12000000, 15000000, 18000000, 14000000, 16000000, 19000000, 
                   21000000, 18000000, 22000000, 25000000, 28000000, 30000000],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat('fr-FR', {
                            style: 'currency',
                            currency: 'XOF'
                        }).format(context.raw);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000) {
                            return (value / 1000000).toFixed(1) + 'M';
                        }
                        if (value >= 1000) {
                            return (value / 1000).toFixed(0) + 'K';
                        }
                        return value;
                    }
                }
            }
        }
    }
});
</script>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

@media (max-width: 1200px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.quick-action {
    background: var(--gray-100);
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-lg);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
    transition: var(--transition-fast);
}

.quick-action:hover {
    background: white;
    border-color: var(--secondary-color);
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.quick-action i {
    font-size: 2rem;
    color: var(--secondary-color);
}

.quick-action span {
    font-weight: 600;
    color: var(--primary-color);
    text-align: center;
}

.rapprochement-status {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.compte-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-md);
    background: var(--gray-100);
    border-radius: var(--border-radius-md);
    transition: var(--transition-fast);
}

.compte-item:hover {
    background: var(--gray-200);
}

.compte-info {
    flex: 1;
}

.compte-numero {
    font-weight: 600;
    color: var(--primary-color);
    font-size: var(--font-size-sm);
}

.compte-libelle {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.compte-soldes {
    display: flex;
    gap: var(--spacing-lg);
    font-size: var(--font-size-sm);
}

.solde-comptable, .solde-releve {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.solde-comptable .label, .solde-releve .label {
    color: var(--gray-600);
    font-size: var(--font-size-xs);
}

.solde-comptable .value, .solde-releve .value {
    font-weight: 600;
    color: var(--primary-color);
}

.compte-action {
    min-width: 100px;
    text-align: center;
}

.cloture-progress {
    padding: var(--spacing-md);
}

.etapes-list {
    margin-top: var(--spacing-lg);
}

.etape-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    border-bottom: 1px solid var(--gray-200);
}

.etape-item:last-child {
    border-bottom: none;
}

.etape-check {
    width: 24px;
    text-align: center;
}

.etape-info {
    flex: 1;
}

.etape-nom {
    font-weight: 500;
    color: var(--gray-700);
    font-size: var(--font-size-sm);
}

.etape-date {
    font-size: var(--font-size-xs);
    color: var(--gray-500);
}

.etape-statut {
    min-width: 80px;
    text-align: right;
}
</style>
