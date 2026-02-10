<?php
// dashboard_consultant.php - Interface personnalisée pour les consultants

// Gestion des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification du rôle
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'consultant') {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Fonctions de récupération des indicateurs
function getIndicateursConsultant($pdo) {
    $indicateurs = [];
    
    try {
        // 1. Chiffre d'affaires du mois
        $sql_ca = "SELECT SUM(credit) as ca_mois 
                   FROM ecritures 
                   WHERE LEFT(compte_num, 1) = '7' 
                   AND MONTH(date_ecriture) = MONTH(CURDATE())
                   AND YEAR(date_ecriture) = YEAR(CURDATE())";
        $stmt = $pdo->query($sql_ca);
        $indicateurs['ca_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['ca_mois'] ?? 0;
        
        // 2. Résultat de l'exercice
        $sql_resultat = "SELECT 
                         (SELECT SUM(credit) FROM ecritures WHERE LEFT(compte_num, 1) = '7') as produits,
                         (SELECT SUM(debit) FROM ecritures WHERE LEFT(compte_num, 1) = '6') as charges";
        $stmt = $pdo->query($sql_resultat);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $indicateurs['resultat'] = ($result['produits'] ?? 0) - ($result['charges'] ?? 0);
        
        // 3. Trésorerie
        $sql_tresorerie = "SELECT 
                           (SELECT SUM(credit - debit) FROM ecritures WHERE LEFT(compte_num, 1) = '5') as tresorerie";
        $stmt = $pdo->query($sql_tresorerie);
        $indicateurs['tresorerie'] = $stmt->fetch(PDO::FETCH_ASSOC)['tresorerie'] ?? 0;
        
        // 4. Rentabilité
        $sql_rentabilite = "SELECT 
                           (SELECT SUM(credit) FROM ecritures WHERE LEFT(compte_num, 1) = '7') as produits_total,
                           (SELECT SUM(debit) FROM ecritures WHERE compte_num LIKE '6%' AND compte_num NOT LIKE '68%') as charges_exploitation";
        $stmt = $pdo->query($sql_rentabilite);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $indicateurs['marge_brute'] = ($result['produits_total'] ?? 0) - ($result['charges_exploitation'] ?? 0);
        
    } catch (PDOException $e) {
        error_log("Erreur indicateurs consultant: " . $e->getMessage());
    }
    
    return $indicateurs;
}

$indicateurs = getIndicateursConsultant($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Consultant - SYSCOA OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --consultant-primary: #1a237e;
            --consultant-secondary: #283593;
            --consultant-accent: #5c6bc0;
            --consultant-light: #e8eaf6;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--consultant-primary) 0%, var(--consultant-secondary) 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 3px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card.ca {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card.resultat {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-card.tresorerie {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .stat-card.rentabilite {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .quick-action {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 1px solid #eef2f7;
        }
        
        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.12);
            border-color: var(--consultant-accent);
        }
        
        .quick-action i {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--consultant-primary);
        }
        
        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .user-profile {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--consultant-primary), var(--consultant-accent));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-2 col-xl-2 sidebar">
                <div class="logo">
                    <h3><i class="fas fa-chart-line"></i> SYSCOA</h3>
                    <small class="opacity-75">Version Consultant</small>
                </div>
                
                <nav class="nav flex-column mt-4">
                    <a class="nav-link active" href="dashboard_consultant.php">
                        <i class="fas fa-home"></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="compte_resultat.php">
                        <i class="fas fa-chart-bar"></i> Compte de résultat
                    </a>
                    <a class="nav-link" href="bilan-comptable.php">
                        <i class="fas fa-balance-scale"></i> Bilan comptable
                    </a>
                    <a class="nav-link" href="tableau_flux_tresorerie.php">
                        <i class="fas fa-money-bill-wave"></i> Flux de trésorerie
                    </a>
                    <a class="nav-link" href="analyse_financiere.php">
                        <i class="fas fa-chart-pie"></i> Analyse financière
                    </a>
                    <a class="nav-link" href="rapports_consultant.php">
                        <i class="fas fa-file-alt"></i> Rapports
                    </a>
                    <a class="nav-link" href="previsions.php">
                        <i class="fas fa-chart-line"></i> Prévisions
                    </a>
                    <div class="mt-5 pt-5">
                        <a class="nav-link" href="parametres.php">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 col-xl-10 main-content">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="h3 mb-1">Tableau de Bord Consultant</h1>
                                <p class="text-muted mb-0">Analyse et conseil financier - SYSCOHADA</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-primary"><?php echo date('d/m/Y'); ?></span>
                                </div>
                                <button class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistiques principales -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card ca p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">CHIFFRE D'AFFAIRES</h6>
                                    <h2 class="mb-0"><?php echo number_format($indicateurs['ca_mois'], 0, ',', ' '); ?> F</h2>
                                    <small class="opacity-75">Mois en cours</small>
                                </div>
                                <div>
                                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-primary">
                                    <i class="fas fa-arrow-up me-1"></i> 12% vs mois dernier
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card resultat p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">RÉSULTAT NET</h6>
                                    <h2 class="mb-0"><?php echo number_format($indicateurs['resultat'], 0, ',', ' '); ?> F</h2>
                                    <small class="opacity-75">Exercice en cours</small>
                                </div>
                                <div>
                                    <i class="fas fa-balance-scale fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge <?php echo $indicateurs['resultat'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $indicateurs['resultat'] >= 0 ? 'Bénéfice' : 'Déficit'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card tresorerie p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">TRÉSORERIE</h6>
                                    <h2 class="mb-0"><?php echo number_format($indicateurs['tresorerie'], 0, ',', ' '); ?> F</h2>
                                    <small class="opacity-75">Disponibilités</small>
                                </div>
                                <div>
                                    <i class="fas fa-piggy-bank fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-info">
                                    <i class="fas fa-clock me-1"></i> 45 jours d'autonomie
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card rentabilite p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="opacity-75 mb-2">MARGE BRUTE</h6>
                                    <h2 class="mb-0"><?php echo number_format($indicateurs['marge_brute'], 0, ',', ' '); ?> F</h2>
                                    <small class="opacity-75">Taux: 35%</small>
                                </div>
                                <div>
                                    <i class="fas fa-percentage fa-2x opacity-75"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white text-success">
                                    <i class="fas fa-check-circle me-1"></i> Objectif atteint
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="row mb-4">
                    <div class="col-12 mb-3">
                        <h4 class="mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Actions Rapides</h4>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="compte_resultat.php" class="text-decoration-none">
                            <div class="quick-action text-center">
                                <i class="fas fa-chart-bar text-primary"></i>
                                <h5>Compte de résultat</h5>
                                <p class="text-muted small">Analyse des produits et charges</p>
                                <span class="badge bg-primary">Consultation</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="analyse_ratio.php" class="text-decoration-none">
                            <div class="quick-action text-center">
                                <i class="fas fa-calculator text-success"></i>
                                <h5>Ratios financiers</h5>
                                <p class="text-muted small">Analyse de la performance</p>
                                <span class="badge bg-success">Calcul</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="rapports_consultant.php" class="text-decoration-none">
                            <div class="quick-action text-center">
                                <i class="fas fa-file-pdf text-danger"></i>
                                <h5>Générer rapport</h5>
                                <p class="text-muted small">Rapport d'analyse complet</p>
                                <span class="badge bg-danger">PDF</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <a href="previsions.php" class="text-decoration-none">
                            <div class="quick-action text-center">
                                <i class="fas fa-chart-line text-info"></i>
                                <h5>Prévisions</h5>
                                <p class="text-muted small">Simulations et scénarios</p>
                                <span class="badge bg-info">Projection</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Contenu principal en deux colonnes -->
                <div class="row">
                    <!-- Graphiques et analyses -->
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="recent-activity">
                                    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Activité récente</h5>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                <span>Rapport mensuel généré</span>
                                            </div>
                                            <small class="text-muted">Il y a 2 heures</small>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <i class="fas fa-chart-bar text-success me-2"></i>
                                                <span>Analyse de rentabilité terminée</span>
                                            </div>
                                            <small class="text-muted">Il y a 1 jour</small>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <i class="fas fa-user-check text-info me-2"></i>
                                                <span>Consultation avec la direction</span>
                                            </div>
                                            <small class="text-muted">Il y a 2 jours</small>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <i class="fas fa-balance-scale text-warning me-2"></i>
                                                <span>Bilan trimestriel validé</span>
                                            </div>
                                            <small class="text-muted">Il y a 3 jours</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="recent-activity">
                                    <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Évolution des indicateurs clés</h5>
                                    <div style="height: 300px; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                                        <p class="text-center text-muted mt-5">
                                            <i class="fas fa-chart-area fa-3x mb-3"></i><br>
                                            Graphique d'évolution des indicateurs
                                            <br>
                                            <small>Intégration ApexCharts en cours</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profil et alertes -->
                    <div class="col-lg-4">
                        <div class="user-profile">
                            <div class="user-avatar">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h5><?php echo $_SESSION['user_nom'] ?? 'Consultant Financier'; ?></h5>
                            <p class="text-muted">Expert-comptable Consultant</p>
                            <div class="mb-3">
                                <span class="badge bg-primary">SYSCOHADA Certifié</span>
                                <span class="badge bg-success">OHADA Expert</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="profil.php" class="btn btn-outline-primary">Mon Profil</a>
                                <a href="parametres.php" class="btn btn-outline-secondary">Paramètres</a>
                            </div>
                        </div>
                        
                        <div class="recent-activity mt-4">
                            <h5 class="mb-3"><i class="fas fa-bell me-2 text-warning"></i>Alertes importantes</h5>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Exercice à clôturer</strong>
                                <p class="small mb-0">Clôture de l'exercice 2025 dans 45 jours</p>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Analyse requise</strong>
                                <p class="small mb-0">Rapport trimestriel à soumettre</p>
                            </div>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Objectifs atteints</strong>
                                <p class="small mb-0">Taux de rentabilité dans les normes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="text-center text-muted small">
                            <p class="mb-0">
                                <i class="fas fa-lock me-1"></i> Système sécurisé SYSCOHADA - Version Consultant 2.1
                                <br>
                                <small>© 2025 - Conforme aux normes OHADA de l'UEMOA</small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh toutes les 2 minutes
        setTimeout(() => {
            window.location.reload();
        }, 120000);
        
        // Notification de nouvelles données
        function checkNewData() {
            fetch('api/check_new_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.hasNewData) {
                        showNotification('Nouvelles données disponibles', 'success');
                    }
                });
        }
        
        // Vérifier toutes les 5 minutes
        setInterval(checkNewData, 300000);
        
        function showNotification(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>
