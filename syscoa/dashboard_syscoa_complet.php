<?php
// dashboard_ohada_complet.php
// TABLEAU DE BORD COMPLET - SYSTÈME COMPTABLE OHADA
// Conforme aux normes SYSCOHADA UEMOA

// Connexion à la base de données
$host = 'localhost';
$dbname = 'sysco_ohada';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Fonctions utilitaires
function getStats($pdo, $table) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function formatMontant($montant) {
    return number_format($montant, 2, ',', ' ') . ' FCFA';
}

// Récupération des statistiques
$stats = [
    'comptes' => getStats($pdo, 'comptes_ohada'),
    'operations' => getStats($pdo, 'operations_comptables'),
    'immobilisations' => getStats($pdo, 'immobilisations'),
    'tiers' => getStats($pdo, 'tiers'),
    'amortissements' => getStats($pdo, 'plan_amortissement')
];

// Calcul de l'équilibre comptable
$equilibre = $pdo->query("
    SELECT 
        SUM(CASE WHEN compte_debit IS NOT NULL THEN montant ELSE 0 END) as total_debit,
        SUM(CASE WHEN compte_credit IS NOT NULL THEN montant ELSE 0 END) as total_credit
    FROM operations_comptables
")->fetch(PDO::FETCH_ASSOC);

// Récupération des immobilisations avec amortissement
$immobilisations = $pdo->query("
    SELECT 
        i.*,
        COUNT(pa.id_amortissement) as total_periodes,
        SUM(pa.montant_dotation) as total_amortissement_prevu,
        SUM(CASE WHEN pa.statut_dotation = 'comptabilisée' THEN pa.montant_dotation ELSE 0 END) as total_comptabilise
    FROM immobilisations i
    LEFT JOIN plan_amortissement pa ON i.actif_id = pa.id_immobilisation_fk
    GROUP BY i.actif_id
")->fetchAll(PDO::FETCH_ASSOC);

// Dernières opérations comptables
$dernieres_operations = $pdo->query("
    SELECT 
        o.*,
        t.nom_raison_sociale as tiers,
        j.intitule as journal
    FROM operations_comptables o
    LEFT JOIN tiers t ON o.code_tiers_fk = t.code_tiers
    LEFT JOIN journaux j ON o.journal_code = j.journal_code
    ORDER BY o.date_operation DESC, o.id_operation DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Système Comptable OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ohada-primary: #2c3e50;
            --ohada-secondary: #34495e;
            --ohada-success: #27ae60;
            --ohada-warning: #f39c12;
            --ohada-danger: #e74c3c;
        }
        
        .bg-ohada {
            background-color: var(--ohada-primary);
        }
        
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .equilibre-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .module-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(44, 62, 80, 0.05);
        }
        
        .badge-statut {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation Principale -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-ohada shadow">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-balance-scale me-2"></i>
                SYSCOHADA UEMOA
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo date('d/m/Y'); ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card equilibre-card shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="display-6 mb-3">
                            <i class="fas fa-chart-line me-2"></i>
                            Tableau de Bord Comptable
                        </h1>
                        <p class="lead mb-0">Système conforme aux normes OHADA - Version UEMOA</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de Statistiques -->
        <div class="row g-4 mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-file-invoice-dollar module-icon"></i>
                        <h5 class="mb-1"><?php echo $stats['comptes']; ?></h5>
                        <small>Comptes OHADA</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-exchange-alt module-icon"></i>
                        <h5 class="mb-1"><?php echo $stats['operations']; ?></h5>
                        <small>Opérations</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-building module-icon"></i>
                        <h5 class="mb-1"><?php echo $stats['immobilisations']; ?></h5>
                        <small>Immobilisations</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-users module-icon"></i>
                        <h5 class="mb-1"><?php echo $stats['tiers']; ?></h5>
                        <small>Tiers</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card bg-secondary text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-calculator module-icon"></i>
                        <h5 class="mb-1"><?php echo $stats['amortissements']; ?></h5>
                        <small>Amortissements</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card stat-card <?php echo ($equilibre['total_debit'] == $equilibre['total_credit']) ? 'bg-success' : 'bg-danger'; ?> text-white">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-balance-scale module-icon"></i>
                        <h5 class="mb-1">
                            <?php echo ($equilibre['total_debit'] == $equilibre['total_credit']) ? 'Équilibré' : 'Déséquilibré'; ?>
                        </h5>
                        <small>État Comptable</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Équilibre Comptable -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-ohada text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-scale-balanced me-2"></i>
                            Équilibre Comptable
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="text-primary"><?php echo formatMontant($equilibre['total_debit']); ?></h3>
                                <small class="text-muted">Total Débit</small>
                            </div>
                            <div class="col-6">
                                <h3 class="text-success"><?php echo formatMontant($equilibre['total_credit']); ?></h3>
                                <small class="text-muted">Total Crédit</small>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h4>
                                <?php if ($equilibre['total_debit'] == $equilibre['total_credit']): ?>
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i>
                                        SYSTÈME ÉQUILIBRÉ
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        DÉSÉQUILIBRE DÉTECTÉ
                                    </span>
                                <?php endif; ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-rocket me-2"></i>
                            Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <button class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-1"></i> Nouvelle Écriture
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-success w-100">
                                    <i class="fas fa-building me-1"></i> Ajouter Immobilisation
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-warning w-100">
                                    <i class="fas fa-file-pdf me-1"></i> Générer Rapport
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-info w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Actualiser Données
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Immobilisations et Amortissements -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>
                            Immobilisations et Amortissements
                        </h5>
                        <span class="badge bg-light text-dark"><?php echo count($immobilisations); ?> actif(s)</span>
                    </div>
                    <div class="card-body">
                        <?php if (count($immobilisations) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Référence</th>
                                            <th>Désignation</th>
                                            <th>Valeur Origine</th>
                                            <th>VNC Actuelle</th>
                                            <th>Amortissement</th>
                                            <th>Progression</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($immobilisations as $immo): 
                                            $pourcentage = ($immo['total_comptabilise'] / $immo['valeur_origine']) * 100;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($immo['code_actif']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($immo['designation']); ?></td>
                                            <td><?php echo formatMontant($immo['valeur_origine']); ?></td>
                                            <td><?php echo formatMontant($immo['valeur_nette_comptable']); ?></td>
                                            <td><?php echo formatMontant($immo['total_comptabilise']); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: <?php echo min($pourcentage, 100); ?>%"
                                                         title="<?php echo round($pourcentage, 1); ?>%">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success badge-statut">
                                                    <?php echo $immo['methode_amortissement']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune immobilisation enregistrée</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières Opérations Comptables -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Dernières Opérations Comptables
                        </h5>
                        <span class="badge bg-light text-dark"><?php echo count($dernieres_operations); ?> opération(s)</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Journal</th>
                                        <th>Pièce</th>
                                        <th>Description</th>
                                        <th>Débit</th>
                                        <th>Crédit</th>
                                        <th>Tiers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dernieres_operations as $op): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($op['date_operation']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary badge-statut">
                                                <?php echo htmlspecialchars($op['journal_code']); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($op['piece_ref'] ?? 'N/A'); ?></small></td>
                                        <td><?php echo htmlspecialchars($op['description']); ?></td>
                                        <td class="text-primary fw-bold">
                                            <?php echo ($op['compte_debit']) ? formatMontant($op['montant']) : '-'; ?>
                                        </td>
                                        <td class="text-success fw-bold">
                                            <?php echo ($op['compte_credit']) ? formatMontant($op['montant']) : '-'; ?>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($op['tiers'] ?? 'N/A'); ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <footer class="mt-5 pt-4 border-top">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-ohada">
                        <i class="fas fa-balance-scale me-1"></i>
                        Système Comptable OHADA
                    </h6>
                    <p class="text-muted small">
                        Conforme aux normes SYSCOHADA UEMOA<br>
                        Développé pour les cabinets comptables d'Afrique de l'Ouest
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted small">
                        <i class="fas fa-database me-1"></i>
                        <?php echo $stats['operations']; ?> opérations · 
                        <?php echo $stats['comptes']; ?> comptes · 
                        <?php echo $stats['immobilisations']; ?> immobilisations
                        <br>
                        <i class="fas fa-clock me-1"></i>
                        Dernière mise à jour: <?php echo date('H:i:s'); ?>
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation des cartes au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Actualisation automatique toutes les 30 secondes
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
