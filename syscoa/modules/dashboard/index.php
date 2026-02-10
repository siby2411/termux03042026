<?php
/**
 * Module Dashboard - Tableau de bord SyscoHADA
 * Cas pratique : SENECOM SA
 */

require_once __DIR__ . '/../../config.php';
verifier_session();

$conn = get_db_connection();

// Récupérer les statistiques
$stats = [];

// Nombre de comptes
$result = $conn->query("SELECT COUNT(*) as total FROM comptes_ohada");
$stats['comptes'] = $result->fetch_assoc()['total'];

// Nombre d'écritures du mois
$mois_courant = date('Y-m');
$result = $conn->query("SELECT COUNT(*) as total FROM ecritures WHERE DATE_FORMAT(date_ecriture, '%Y-%m') = '$mois_courant'");
$stats['ecritures_mois'] = $result->fetch_assoc()['total'];

// Total débit/crédit du mois
$result = $conn->query("SELECT SUM(montant_debit) as total_debit, SUM(montant_credit) as total_credit FROM ecritures WHERE DATE_FORMAT(date_ecriture, '%Y-%m') = '$mois_courant'");
$totaux = $result->fetch_assoc();
$stats['total_debit'] = $totaux['total_debit'] ?? 0;
$stats['total_credit'] = $totaux['total_credit'] ?? 0;

// Exercice actif
$result = $conn->query("SELECT * FROM exercices_comptables WHERE statut = 'actif' LIMIT 1");
$exercice = $result->fetch_assoc();

// Dernières écritures
$result = $conn->query("SELECT e.*, c1.libelle as compte_debit_libelle, c2.libelle as compte_credit_libelle 
                       FROM ecritures e
                       LEFT JOIN comptes_ohada c1 ON e.compte_debit_id = c1.id
                       LEFT JOIN comptes_ohada c2 ON e.compte_credit_id = c2.id
                       ORDER BY e.date_ecriture DESC, e.id DESC
                       LIMIT 10");
$dernieres_ecritures = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Journaliser l'accès
logger_action('Accès dashboard', 'Tableau de bord consulté');
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> Tableau de bord</h2>
        <p class="lead">Bienvenue dans SyscoHADA - Système Comptable OHADA</p>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>SENECOM SA</strong> | Exercice actif : 
            <?php if ($exercice): ?>
                <span class="badge bg-success"><?php echo htmlspecialchars($exercice['libelle']); ?></span>
                (<?php echo date('d/m/Y', strtotime($exercice['date_debut'])); ?> - 
                 <?php echo date('d/m/Y', strtotime($exercice['date_fin'])); ?>)
            <?php else: ?>
                <span class="badge bg-danger">Aucun exercice actif</span>
                <a href="<?php echo BASE_URL; ?>index.php?module=exercices_comptables" class="btn btn-sm btn-warning ms-2">
                    <i class="bi bi-plus-circle"></i> Créer un exercice
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cartes statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">COMPTES</h6>
                        <h2 class="mb-0"><?php echo $stats['comptes']; ?></h2>
                    </div>
                    <i class="bi bi-book" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Plan comptable OHADA</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">ÉCRITURES MOIS</h6>
                        <h2 class="mb-0"><?php echo $stats['ecritures_mois']; ?></h2>
                    </div>
                    <i class="bi bi-journal-text" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2"><?php echo date('F Y'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">TOTAL DÉBIT</h6>
                        <h2 class="mb-0"><?php echo format_montant_ohada($stats['total_debit']); ?></h2>
                    </div>
                    <i class="bi bi-arrow-down-left" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Mois en cours</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stat bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">TOTAL CRÉDIT</h6>
                        <h2 class="mb-0"><?php echo format_montant_ohada($stats['total_credit']); ?></h2>
                    </div>
                    <i class="bi bi-arrow-up-right" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <p class="card-text mt-2">Mois en cours</p>
            </div>
        </div>
    </div>
</div>

<!-- Graphique et activités -->
<div class="row">
    <!-- Graphique simple -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-bar-chart"></i> Activité mensuelle</h5>
            </div>
            <div class="card-body">
                <canvas id="chartActivite" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Accès rapide -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-lightning"></i> Accès rapide</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>index.php?module=saisie_ecriture" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvelle écriture
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=journaux" class="btn btn-outline-primary">
                        <i class="bi bi-journal"></i> Consulter journaux
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=balance" class="btn btn-outline-success">
                        <i class="bi bi-scale"></i> Balance générale
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=rapprochement" class="btn btn-outline-info">
                        <i class="bi bi-arrow-left-right"></i> Rapprochement
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?module=cloture" class="btn btn-outline-warning">
                        <i class="bi bi-lock"></i> Travaux clôture
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières écritures -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Dernières écritures</h5>
            </div>
            <div class="card-body">
                <?php if (empty($dernieres_ecritures)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Aucune écriture enregistrée pour le moment.
                        <a href="<?php echo BASE_URL; ?>index.php?module=saisie_ecriture" class="btn btn-sm btn-primary ms-2">
                            <i class="bi bi-plus"></i> Créer la première écriture
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-ohada">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Pièce</th>
                                    <th>Compte débit</th>
                                    <th>Compte crédit</th>
                                    <th>Libellé</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieres_ecritures as $ecriture): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($ecriture['date_ecriture'])); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $ecriture['numero_piece']; ?></span></td>
                                    <td><?php echo htmlspecialchars($ecriture['compte_debit_libelle'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ecriture['compte_credit_libelle'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ecriture['libelle']); ?></td>
                                    <td class="text-end"><?php echo format_montant_ohada($ecriture['montant_debit']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="<?php echo BASE_URL; ?>index.php?module=journaux" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right"></i> Voir toutes les écritures
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique d'activité
const ctx = document.getElementById('chartActivite').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Nombre d\'écritures',
            data: [12, 19, 15, 25, 22, 30, 28, 32, 30, 35, 40, 45],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Activité comptable 2024 - SENECOM SA'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Nombre d\'écritures'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Mois'
                }
            }
        }
    }
});
</script>
