<?php
// dashboard_comptable.php - Interface personnalisée pour les comptables

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification du rôle
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'comptable') {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Fonctions de récupération des indicateurs comptables
function getIndicateursComptable($pdo) {
    $indicateurs = [];
    
    try {
        // 1. Écritures du jour
        $sql_ecritures_jour = "SELECT COUNT(*) as nb_ecritures 
                               FROM ecritures 
                               WHERE DATE(date_ecriture) = CURDATE()";
        $stmt = $pdo->query($sql_ecritures_jour);
        $indicateurs['ecritures_jour'] = $stmt->fetch(PDO::FETCH_ASSOC)['nb_ecritures'] ?? 0;
        
        // 2. Écritures à valider
        $sql_a_valider = "SELECT COUNT(*) as nb_a_valider 
                          FROM ecritures 
                          WHERE statut = 'saisie' 
                          AND DATE(date_ecriture) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $stmt = $pdo->query($sql_a_valider);
        $indicateurs['a_valider'] = $stmt->fetch(PDO::FETCH_ASSOC)['nb_a_valider'] ?? 0;
        
        // 3. Total écritures du mois
        $sql_ecritures_mois = "SELECT COUNT(*) as nb_ecritures_mois 
                               FROM ecritures 
                               WHERE MONTH(date_ecriture) = MONTH(CURDATE())
                               AND YEAR(date_ecriture) = YEAR(CURDATE())";
        $stmt = $pdo->query($sql_ecritures_mois);
        $indicateurs['ecritures_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['nb_ecritures_mois'] ?? 0;
        
        // 4. Balance non équilibrée
        $sql_balance = "SELECT COUNT(DISTINCT date_ecriture) as jours_non_equilibres 
                        FROM ecritures 
                        WHERE id_exercice = (SELECT id_exercice FROM exercices_comptables WHERE statut = 'ouvert')
                        GROUP BY DATE(date_ecriture)
                        HAVING ABS(SUM(debit) - SUM(credit)) > 0";
        $stmt = $pdo->query($sql_balance);
        $indicateurs['jours_non_equilibres'] = $stmt->rowCount();
        
        // 5. Dernières écritures
        $sql_dernieres = "SELECT date_ecriture, journal_code, num_piece, libelle, debit, credit 
                          FROM ecritures 
                          ORDER BY date_ecriture DESC, ecriture_id DESC 
                          LIMIT 5";
        $stmt = $pdo->query($sql_dernieres);
        $indicateurs['dernieres_ecritures'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur indicateurs comptable: " . $e->getMessage());
    }
    
    return $indicateurs;
}

$indicateurs = getIndicateursComptable($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Comptable - SYSCOA OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --comptable-primary: #00695c;
            --comptable-secondary: #00897b;
            --comptable-accent: #4db6ac;
            --comptable-light: #e0f2f1;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .comptable-sidebar {
            background: linear-gradient(180deg, var(--comptable-primary) 0%, var(--comptable-secondary) 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        }
        
        .comptable-sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .comptable-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .comptable-sidebar .nav-link:hover, .comptable-sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .comptable-stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .comptable-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .stat-ecritures {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
        }
        
        .stat-validation {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            color: white;
        }
        
        .stat-mois {
            background: linear-gradient(135deg, #2196F3 0%, #0D47A1 100%);
            color: white;
        }
        
        .stat-equilibre {
            background: linear-gradient(135deg, #9C27B0 0%, #6A1B9A 100%);
            color: white;
        }
        
        .quick-saisie {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 2px solid var(--comptable-light);
        }
        
        .table-ecritures tbody tr {
            transition: all 0.2s;
        }
        
        .table-ecritures tbody tr:hover {
            background-color: var(--comptable-light);
            transform: scale(1.005);
        }
        
        .badge-journal {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        
        .alert-comptable {
            border-left: 4px solid var(--comptable-accent);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Comptable -->
            <div class="col-lg-2 col-xl-2 comptable-sidebar">
                <div class="logo">
                    <h3><i class="fas fa-book"></i> SYSCOA</h3>
                    <small class="opacity-75">Version Comptable</small>
                </div>
                
                <nav class="nav flex-column mt-4">
                    <a class="nav-link active" href="dashboard_comptable.php">
                        <i class="fas fa-home"></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="saisie_ecriture.php">
                        <i class="fas fa-edit"></i> Saisie écritures
                    </a>
                    <a class="nav-link" href="journal_comptable.php">
                        <i class="fas fa-book-open"></i> Journal
                    </a>
                    <a class="nav-link" href="grand_livre.php">
                        <i class="fas fa-book"></i> Grand livre
                    </a>
                    <a class="nav-link" href="balance.php">
                        <i class="fas fa-balance-scale"></i> Balance
                    </a>
                    <a class="nav-link" href="plan_comptable.php">
                        <i class="fas fa-list"></i> Plan comptable
                    </a>
                    <a class="nav-link" href="bilan-comptable.php">
                        <i class="fas fa-file-invoice-dollar"></i> Bilan
                    </a>
                    <a class="nav-link" href="compte_resultat.php">
                        <i class="fas fa-chart-bar"></i> Compte résultat
                    </a>
                    <a class="nav-link" href="exercices.php">
                        <i class="fas fa-calendar-alt"></i> Exercices
                    </a>
                    <div class="mt-5 pt-5">
                        <a class="nav-link" href="parametres_comptable.php">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 col-xl-10 p-4">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1">Tableau de Bord Comptable</h1>
                                <p class="text-muted mb-0">Gestion comptable - SYSCOHADA OHADA</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-success"><?php echo date('d/m/Y'); ?></span>
                                    <span class="badge bg-info">Exercice 2025</span>
                                </div>
                                <a href="saisie_ecriture.php" class="btn btn-success">
                                    <i class="fas fa-plus-circle me-2"></i>Nouvelle écriture
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques comptables -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="comptable-stat-card stat-ecritures p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">ÉCRITURES AUJOURD'HUI</h6>
                                    <h2 class="mb-0"><?php echo $indicateurs['ecritures_jour']; ?></h2>
                                    <small class="opacity-75">Enregistrées ce jour</small>
                                </div>
                                <div>
                                    <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-success">
                                    <i class="fas fa-check-circle me-1"></i> À jour
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="comptable-stat-card stat-validation p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">À VALIDER</h6>
                                    <h2 class="mb-0"><?php echo $indicateurs['a_valider']; ?></h2>
                                    <small class="opacity-75">Écritures en attente</small>
                                </div>
                                <div>
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Attention
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="comptable-stat-card stat-mois p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">CE MOIS</h6>
                                    <h2 class="mb-0"><?php echo $indicateurs['ecritures_mois']; ?></h2>
                                    <small class="opacity-75">Écritures mensuelles</small>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-info">
                                    <i class="fas fa-chart-line me-1"></i> +15% vs mois dernier
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="comptable-stat-card stat-equilibre p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">ÉQUILIBRAGE</h6>
                                    <h2 class="mb-0"><?php echo $indicateurs['jours_non_equilibres']; ?></h2>
                                    <small class="opacity-75">Jours non équilibrés</small>
                                </div>
                                <div>
                                    <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white <?php echo $indicateurs['jours_non_equilibres'] == 0 ? 'text-success' : 'text-danger'; ?>">
                                    <i class="fas fa-<?php echo $indicateurs['jours_non_equilibres'] == 0 ? 'check' : 'exclamation'; ?>-circle me-1"></i>
                                    <?php echo $indicateurs['jours_non_equilibres'] == 0 ? 'Équilibré' : 'À corriger'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contenu principal en deux colonnes -->
                <div class="row">
                    <!-- Colonne gauche: Saisie rapide et dernières écritures -->
                    <div class="col-lg-8">
                        <!-- Saisie rapide d'écriture -->
                        <div class="quick-saisie">
                            <h5 class="mb-4"><i class="fas fa-edit me-2 text-primary"></i>Saisie rapide d'écriture</h5>
                            <form action="api/saisie_rapide.php" method="POST" id="formSaisieRapide">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="date_ecriture" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Journal</label>
                                        <select class="form-select" name="journal_code" required>
                                            <option value="AC">AC - Achats</option>
                                            <option value="VE">VE - Ventes</option>
                                            <option value="BN">BN - Banque</option>
                                            <option value="CA">CA - Caisse</option>
                                            <option value="OD">OD - Opérations diverses</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Numéro de pièce</label>
                                        <input type="text" class="form-control" name="num_piece" 
                                               placeholder="Ex: FAC2025001" required>
                                    </div>
                                </div>
                                
                                <div class="row g-3 mt-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Compte débité</label>
                                        <select class="form-select" name="compte_debit" required>
                                            <option value="">Sélectionnez un compte...</option>
                                            <option value="60700000">60700000 - Achats de marchandises</option>
                                            <option value="40100000">40100000 - Fournisseurs</option>
                                            <option value="51200000">51200000 - Banque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Compte crédité</label>
                                        <select class="form-select" name="compte_credit" required>
                                            <option value="">Sélectionnez un compte...</option>
                                            <option value="70700000">70700000 - Ventes de marchandises</option>
                                            <option value="41100000">41100000 - Clients</option>
                                            <option value="51200000">51200000 - Banque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Montant</label>
                                        <input type="number" class="form-control" name="montant" 
                                               step="0.01" placeholder="0.00" required>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <label class="form-label">Libellé</label>
                                        <input type="text" class="form-control" name="libelle" 
                                               placeholder="Libellé de l'écriture" required>
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Enregistrer l'écriture
                                        </button>
                                        <button type="reset" class="btn btn-outline-secondary ms-2">
                                            <i class="fas fa-times me-2"></i>Annuler
                                        </button>
                                        <a href="saisie_ecriture.php" class="btn btn-link ms-2">
                                            <i class="fas fa-external-link-alt me-1"></i>Saisie complète
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Dernières écritures -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Dernières écritures</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-ecritures mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="100">Date</th>
                                                <th width="80">Journal</th>
                                                <th>Compte</th>
                                                <th>Libellé</th>
                                                <th width="120" class="text-end">Débit</th>
                                                <th width="120" class="text-end">Crédit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($indicateurs['dernieres_ecritures'])): ?>
                                                <?php foreach ($indicateurs['dernieres_ecritures'] as $ecriture): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($ecriture['date_ecriture'])); ?></td>
                                                    <td>
                                                        <span class="badge badge-journal bg-info">
                                                            <?php echo htmlspecialchars($ecriture['journal_code']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo 'Compte ' . substr($ecriture['compte_num'] ?? 'N/A', 0, 8); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(substr($ecriture['libelle'], 0, 30)); ?></td>
                                                    <td class="text-end">
                                                        <?php if ($ecriture['debit'] > 0): ?>
                                                        <span class="text-danger fw-bold">
                                                            <?php echo number_format($ecriture['debit'], 2, ',', ' '); ?>
                                                        </span>
                                                        <?php else: ?>
                                                        <span class="text-muted">0,00</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <?php if ($ecriture['credit'] > 0): ?>
                                                        <span class="text-success fw-bold">
                                                            <?php echo number_format($ecriture['credit'], 2, ',', ' '); ?>
                                                        </span>
                                                        <?php else: ?>
                                                        <span class="text-muted">0,00</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-3"></i>
                                                        <p>Aucune écriture récente</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="journal_comptable.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>Voir le journal complet
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colonne droite: Alertes et actions rapides -->
                    <div class="col-lg-4">
                        <!-- Alertes importantes -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>Alertes comptables</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($indicateurs['a_valider'] > 0): ?>
                                <div class="alert alert-warning alert-comptable">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Écritures à valider</h6>
                                            <p class="mb-0"><?php echo $indicateurs['a_valider']; ?> écriture(s) en attente de validation</p>
                                            <a href="validation_ecritures.php" class="btn btn-sm btn-warning mt-2">
                                                <i class="fas fa-check-circle me-1"></i>Vérifier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($indicateurs['jours_non_equilibres'] > 0): ?>
                                <div class="alert alert-danger alert-comptable">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Journaux non équilibrés</h6>
                                            <p class="mb-0"><?php echo $indicateurs['jours_non_equilibres']; ?> jour(s) avec déséquilibre</p>
                                            <a href="balance.php" class="btn btn-sm btn-danger mt-2">
                                                <i class="fas fa-balance-scale me-1"></i>Contrôler
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="alert alert-info alert-comptable">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Clôture d'exercice</h6>
                                            <p class="mb-0">Exercice 2025 à clôturer dans 45 jours</p>
                                            <a href="exercices.php" class="btn btn-sm btn-info mt-2">
                                                <i class="fas fa-calendar-alt me-1"></i>Planifier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions rapides -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2 text-success"></i>Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="journal_comptable.php?periode=jour" class="btn btn-outline-primary text-start">
                                        <i class="fas fa-print me-2"></i>Imprimer journal du jour
                                    </a>
                                    <a href="balance.php?type=synthese" class="btn btn-outline-success text-start">
                                        <i class="fas fa-calculator me-2"></i>Balance de vérification
                                    </a>
                                    <a href="grand_livre.php?compte=512" class="btn btn-outline-info text-start">
                                        <i class="fas fa-book me-2"></i>Grand livre banque
                                    </a>
                                    <a href="rapport_tva.php" class="btn btn-outline-warning text-start">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>Déclaration TVA
                                    </a>
                                    <a href="export_comptabilite.php" class="btn btn-outline-secondary text-start">
                                        <i class="fas fa-download me-2"></i>Export FEC
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statistiques mensuelles -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques du mois</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6>Écritures par journal</h6>
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: 40%">
                                            <small>VE (40%)</small>
                                        </div>
                                        <div class="progress-bar bg-info" style="width: 25%">
                                            <small>AC (25%)</small>
                                        </div>
                                        <div class="progress-bar bg-warning" style="width: 20%">
                                            <small>BN (20%)</small>
                                        </div>
                                        <div class="progress-bar bg-secondary" style="width: 15%">
                                            <small>OD (15%)</small>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h6>Volume mensuel</h6>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Écritures: <?php echo $indicateurs['ecritures_mois']; ?></span>
                                        <span class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>+15%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Montant total: 5 250 000 F</span>
                                        <span class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>+8%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pied de page -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="text-center text-muted small">
                            <p class="mb-0">
                                <i class="fas fa-shield-alt me-1"></i> Système Comptable SYSCOHADA - Version Comptable 2.1
                                <br>
                                <small>© 2025 - Conforme aux normes OHADA - UEMOA - DGI</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion du formulaire de saisie rapide
        document.getElementById('formSaisieRapide').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/saisie_rapide.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Écriture enregistrée avec succès!', 'success');
                    this.reset();
                    // Rafraîchir la liste des dernières écritures
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification('Erreur: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showNotification('Erreur réseau', 'danger');
            });
        });
        
        function showNotification(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
        
        // Auto-sauvegarde toutes les 30 secondes
        setInterval(() => {
            const form = document.getElementById('formSaisieRapide');
            const inputs = form.querySelectorAll('input, select');
            let hasValue = false;
            
            inputs.forEach(input => {
                if (input.value && !input.readOnly) {
                    hasValue = true;
                }
            });
            
            if (hasValue) {
                console.log('Auto-sauvegarde...');
                // Implémenter l'auto-sauvegarde si nécessaire
            }
        }, 30000);
    </script>
</body>
</html>
