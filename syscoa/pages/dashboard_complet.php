<?php
// /var/www/syscoa/pages/dashboard_complet.php
$stats = get_dashboard_stats();
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1>Tableau de bord SYSCOHADA</h1>
            <p class="mb-0">Système comptable conforme aux normes OHADA - Vue d'ensemble de votre entreprise</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <button class="btn btn-outline-light">
                    <i class="fas fa-download me-2"></i>Exporter
                </button>
                <button class="btn btn-light">
                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="quick-stats">
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    <div class="count"><?php echo $stats['exercices']; ?></div>
                    <div class="label">Exercices</div>
                    <small>Dernier: <?php echo $stats['dernier_exercice']; ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice-dollar text-success"></i>
                    <div class="count"><?php echo number_format($stats['ecritures'], 0, ',', ' '); ?></div>
                    <div class="label">Écritures</div>
                    <small>Total: <?php echo number_format($stats['total_debit'], 0, ',', ' '); ?> F</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-users text-warning"></i>
                    <div class="count"><?php echo $stats['tiers']; ?></div>
                    <div class="label">Tiers</div>
                    <small>Clients & Fournisseurs</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body text-center">
                    <i class="fas fa-building text-info"></i>
                    <div class="count"><?php echo $stats['immobilisations']; ?></div>
                    <div class="label">Immobilisations</div>
                    <small>Actifs enregistrés</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modules d'accès rapide -->
<div class="row mb-4">
    <div class="col-md-12">
        <h4 class="mb-3"><i class="fas fa-rocket me-2"></i>Accès rapide aux modules</h4>
        <div class="row">
            <?php foreach (SYSCOHADA_MODULES as $module_key => $module): ?>
            <div class="col-md-3 col-sm-6">
                <a href="index.php?module=<?php echo $module_key; ?>" class="text-decoration-none">
                    <div class="module-card">
                        <i class="fas <?php echo $module['icon']; ?>"></i>
                        <h5><?php echo $module['name']; ?></h5>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Graphiques et données -->
<div class="row">
    <div class="col-md-8">
        <!-- Graphique des écritures -->
        <div class="chart-container">
            <h5><i class="fas fa-chart-line me-2"></i>Activité comptable mensuelle</h5>
            <canvas id="activityChart" height="250"></canvas>
        </div>
        
        <!-- Dernières écritures -->
        <div class="table-container">
            <h5><i class="fas fa-history me-2"></i>Dernières écritures</h5>
            <?php
            $dernieres_ecritures = get_results("
                SELECT e.date_ecriture, e.libelle, c.nom_compte, e.debit, e.credit 
                FROM ecritures e
                JOIN comptes_ohada c ON e.compte_num = c.numero_compte
                ORDER BY e.date_ecriture DESC 
                LIMIT 10
            ");
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th>Compte</th>
                        <th class="text-end">Débit</th>
                        <th class="text-end">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres_ecritures as $ecriture): ?>
                    <tr>
                        <td><?php echo $ecriture['date_ecriture']; ?></td>
                        <td><?php echo substr($ecriture['libelle'], 0, 30) . '...'; ?></td>
                        <td><small><?php echo $ecriture['nom_compte']; ?></small></td>
                        <td class="text-end text-danger"><?php echo $ecriture['debit'] > 0 ? number_format($ecriture['debit'], 2, ',', ' ') : ''; ?></td>
                        <td class="text-end text-success"><?php echo $ecriture['credit'] > 0 ? number_format($ecriture['credit'], 2, ',', ' ') : ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Alertes et notifications -->
        <div class="chart-container mb-4">
            <h5><i class="fas fa-bell me-2"></i>Alertes système</h5>
            <div class="alert alert-custom alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Clôture imminente</strong><br>
                L'exercice <?php echo date('Y'); ?> se termine bientôt.
            </div>
            <div class="alert alert-custom alert-info">
                <i class="fas fa-file-invoice me-2"></i>
                <strong>Déclaration TVA</strong><br>
                Prochaine échéance: 20 <?php echo date('F'); ?>.
            </div>
            <div class="alert alert-custom alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Système à jour</strong><br>
                Tous les modules sont opérationnels.
            </div>
        </div>
        
        <!-- Prochaines échéances -->
        <div class="chart-container">
            <h5><i class="fas fa-calendar-check me-2"></i>Prochaines échéances</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Déclaration TVA
                    <span class="badge bg-warning">Dans 5 jours</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Paiement impôts
                    <span class="badge bg-warning">Dans 10 jours</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Clôture trimestrielle
                    <span class="badge bg-info">Dans 15 jours</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Rapport mensuel
                    <span class="badge bg-info">Dans 3 jours</span>
                </li>
            </ul>
        </div>
        
        <!-- Indicateurs SYSCOHADA -->
        <div class="chart-container mt-4">
            <h5><i class="fas fa-chart-pie me-2"></i>Conformité SYSCOHADA</h5>
            <div class="progress mb-2">
                <div class="progress-bar bg-success" role="progressbar" style="width: 95%">
                    Plan comptable: 95%
                </div>
            </div>
            <div class="progress mb-2">
                <div class="progress-bar bg-info" role="progressbar" style="width: 85%">
                    Normes OHADA: 85%
                </div>
            </div>
            <div class="progress mb-2">
                <div class="progress-bar bg-warning" role="progressbar" style="width: 75%">
                    Documentation: 75%
                </div>
            </div>
            <div class="progress">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 100%">
                    Intégration: 100%
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour le graphique -->
<script>
$(document).ready(function() {
    // Graphique d'activité
    var ctx = document.getElementById('activityChart').getContext('2d');
    var activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Débits',
                data: [120000, 190000, 150000, 180000, 160000, 195000, 210000, 185000, 200000, 220000, 240000, 250000],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4
            }, {
                label: 'Crédits',
                data: [115000, 185000, 148000, 175000, 158000, 190000, 205000, 180000, 195000, 215000, 235000, 245000],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' F';
                        }
                    }
                }
            }
        }
    });
    
    // Initialiser DataTables sur les tableaux
    $('table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        pageLength: 10,
        order: [[0, 'desc']]
    });
    
    // Animation des cartes
    $('.module-card').hover(
        function() {
            $(this).css('transform', 'scale(1.05)');
        },
        function() {
            $(this).css('transform', 'scale(1)');
        }
    );
});
</script>
