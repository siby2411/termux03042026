<?php
// index.php - Version corrigée avec bons liens
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Vérifier que les fonctions existent
if (!function_exists('get_current_sysco_user')) {
    function get_current_sysco_user() {
        global $pdo;
        if (isset($_SESSION['user_id'])) {
            try {
                $sql = "SELECT id_user, username, nom_complet, email, role FROM users WHERE id_user = :id LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $_SESSION['user_id']]);
                return $stmt->fetch();
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }
}

if (!function_exists('format_montant')) {
    function format_montant($montant, $devise = 'FCFA') {
        if ($montant === null || $montant === '') {
            return '0,00 ' . $devise;
        }
        return number_format(floatval($montant), 2, ',', ' ') . ' ' . $devise;
    }
}

$user = get_current_sysco_user();
if (!$user) {
    // Si pas d'utilisateur, rediriger vers login
    header('Location: login.php');
    exit();
}

$exercice = date('Y');

// Récupérer quelques statistiques
$stats = [];
try {
    // Nombre de comptes
    $stmt = $pdo->query("SELECT COUNT(*) as nb FROM comptes_ohada");
    $stats['comptes'] = $stmt->fetch()['nb'] ?? 0;
    
    // Nombre d'écritures ce mois
    $stmt = $pdo->query("SELECT COUNT(*) as nb FROM ecritures WHERE MONTH(date_ecriture) = MONTH(NOW())");
    $stats['ecritures_mois'] = $stmt->fetch()['nb'] ?? 0;
    
    // Total des ventes ce mois (comptes 7xxx)
    $stmt = $pdo->query("SELECT SUM(credit) as total FROM ecritures WHERE compte_num LIKE '7%' AND MONTH(date_ecriture) = MONTH(NOW())");
    $stats['ventes_mois'] = $stmt->fetch()['total'] ?? 0;
    
    // Solde de trésorerie (comptes 5xxx)
    $stmt = $pdo->query("SELECT SUM(solde) as solde 
                         FROM soldes_comptes 
                         WHERE LEFT(numero_compte, 1) = '5' 
                         AND exercice_id IN (SELECT id_exercice FROM exercices_comptables WHERE annee = YEAR(NOW()))");
    $result = $stmt->fetch();
    $stats['tresorerie'] = $result ? floatval($result['solde']) : 0;
    
} catch (Exception $e) {
    // En cas d'erreur, utiliser des valeurs par défaut
    $stats['comptes'] = 0;
    $stats['ecritures_mois'] = 0;
    $stats['ventes_mois'] = 0;
    $stats['tresorerie'] = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - SYSCO OHADA</title>
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #2d3748;
            --success: #38a169;
            --warning: #dd6b20;
            --info: #3182ce;
            --light: #f7fafc;
            --dark: #2d3748;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary), #2b6cb0);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar {
            width: 250px;
            background: white;
            height: calc(100vh - 70px);
            position: fixed;
            left: 0;
            top: 70px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: var(--secondary);
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .nav-link:hover {
            background: #edf2f7;
            color: var(--primary);
        }
        
        .nav-link.active {
            background: #ebf8ff;
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }
        
        .nav-icon {
            margin-right: 12px;
            font-size: 18px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            color: var(--primary);
            font-size: 28px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.comptes { background: #c6f6d5; color: #22543d; }
        .stat-icon.ecritures { background: #fed7d7; color: #742a2a; }
        .stat-icon.ventes { background: #e9d8fd; color: #553c9a; }
        .stat-icon.tresorerie { background: #bee3f8; color: #2c5282; }
        
        .stat-info h3 {
            font-size: 14px;
            color: #718096;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .action-btn {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        .action-icon {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }
        
        .recent-activities {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .recent-activities h2 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-time {
            color: #718096;
            font-size: 14px;
            min-width: 120px;
        }
        
        .activity-text {
            flex: 1;
        }
        
        .logout-btn {
            background: #fed7d7;
            color: #742a2a;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            background: #fecaca;
        }
        
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">
            📊 SYSCO OHADA
        </div>
        <div class="user-info">
            <span>Bienvenue, <?php echo htmlspecialchars($user['nom_complet'] ?? 'Utilisateur'); ?></span>
            <span class="role-badge"><?php echo htmlspecialchars($user['role'] ?? 'Utilisateur'); ?></span>
            <a href="logout.php" class="logout-btn">🚪 Déconnexion</a>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <span class="nav-icon">🏠</span>
                    Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a href="journal_comptable.php" class="nav-link">
                    <span class="nav-icon">📒</span>
                    Journal Comptable
                </a>
            </li>
            <li class="nav-item">
                <a href="saisie_ecriture.php" class="nav-link">
                    <span class="nav-icon">✍️</span>
                    Saisie Écriture
                </a>
            </li>
            <li class="nav-item">
                <a href="grand_livre.php" class="nav-link">
                    <span class="nav-icon">📚</span>
                    Grand Livre
                </a>
            </li>
            <li class="nav-item">
                <a href="balance.php" class="nav-link">
                    <span class="nav-icon">⚖️</span>
                    Balance
                </a>
            </li>
            <li class="nav-item">
                <a href="bilan-comptable.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    Bilan Comptable
                </a>
            </li>
            <li class="nav-item">
                <a href="compte_resultat.php" class="nav-link">
                    <span class="nav-icon">📈</span>
                    Compte de Résultat
                </a>
            </li>
            <li class="nav-item">
                <a href="plan_comptable.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    Plan Comptable
                </a>
            </li>
            <li class="nav-item">
                <a href="analyse_financiere.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    Analyse Financière
                </a>
            </li>
            <li class="nav-item">
                <a href="budget.php" class="nav-link">
                    <span class="nav-icon">💰</span>
                    Contrôle Budgétaire
                </a>
            </li>
            <li class="nav-item">
                <a href="tableau_flux_tresorerie.php" class="nav-link">
                    <span class="nav-icon">💸</span>
                    Flux de Trésorerie
                </a>
            </li>
            <li class="nav-item">
                <a href="tresorerie.php" class="nav-link">
                    <span class="nav-icon">🏦</span>
                    Trésorerie
                </a>
            </li>
            <?php if ($user['role'] == 'admin'): ?>
            <li class="nav-item">
                <a href="administration.php" class="nav-link">
                    <span class="nav-icon">⚙️</span>
                    Administration
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="visualisation_tables.php" class="nav-link">
                    <span class="nav-icon">🔍</span>
                    Visualisation Tables
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Contenu principal -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>Tableau de bord</h1>
            <div style="color: #718096;">
                Exercice <?php echo $exercice; ?> | 
                <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon comptes">
                    📋
                </div>
                <div class="stat-info">
                    <h3>Comptes</h3>
                    <div class="stat-value"><?php echo $stats['comptes']; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon ecritures">
                    ✍️
                </div>
                <div class="stat-info">
                    <h3>Écritures ce mois</h3>
                    <div class="stat-value"><?php echo $stats['ecritures_mois']; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon ventes">
                    💰
                </div>
                <div class="stat-info">
                    <h3>Ventes ce mois</h3>
                    <div class="stat-value"><?php echo format_montant($stats['ventes_mois']); ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon tresorerie">
                    🏦
                </div>
                <div class="stat-info">
                    <h3>Trésorerie</h3>
                    <div class="stat-value"><?php echo format_montant($stats['tresorerie']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <h2 style="color: var(--primary); margin-bottom: 20px;">Actions rapides</h2>
        <div class="quick-actions">
            <a href="saisie_ecriture.php" class="action-btn">
                <span class="action-icon">➕</span>
                Nouvelle écriture
            </a>
            
            <a href="journal_comptable.php" class="action-btn">
                <span class="action-icon">📒</span>
                Voir le journal
            </a>
            
            <a href="analyse_financiere.php" class="action-btn">
                <span class="action-icon">📈</span>
                Rapports financiers
            </a>
            
            <a href="budget.php" class="action-btn">
                <span class="action-icon">📋</span>
                Contrôle budget
            </a>
        </div>
        
        <!-- Activités récentes -->
        <div class="recent-activities">
            <h2>Activités récentes</h2>
            
            <div class="activity-item">
                <div class="activity-time"><?php echo date('H:i'); ?></div>
                <div class="activity-text">Connexion réussie au système</div>
            </div>
            
            <div class="activity-item">
                <div class="activity-time"><?php echo date('H:i', strtotime('-30 minutes')); ?></div>
                <div class="activity-text">Exercice <?php echo $exercice; ?> actif</div>
            </div>
            
            <div class="activity-item">
                <div class="activity-time"><?php echo date('H:i', strtotime('-1 hour')); ?></div>
                <div class="activity-text">Système SYSCO-OHADA opérationnel</div>
            </div>
            
            <div class="activity-item">
                <div class="activity-time">Hier, 14:30</div>
                <div class="activity-text">Mise à jour des paramètres système</div>
            </div>
            
            <div class="activity-item">
                <div class="activity-time">Hier, 10:15</div>
                <div class="activity-text">Génération des états financiers</div>
            </div>
        </div>
        
        <!-- Informations système -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3 style="color: var(--primary); margin-bottom: 15px;">📋 Informations système</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <small style="color: #718096;">Version SYSCO</small>
                    <div style="font-weight: bold;">1.0.0</div>
                </div>
                <div>
                    <small style="color: #718096;">Statut</small>
                    <div style="color: var(--success); font-weight: bold;">● Opérationnel</div>
                </div>
                <div>
                    <small style="color: #718096;">Base de données</small>
                    <div style="font-weight: bold;">sysco_ohada</div>
                </div>
                <div>
                    <small style="color: #718096;">Utilisateurs actifs</small>
                    <div style="font-weight: bold;">3</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
