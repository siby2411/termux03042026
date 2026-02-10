<?php
// admin/dashboard.php
session_start();
require_once '../config/database.php';

// Vérifier l'authentification et les permissions
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Récupérer les statistiques
$stats = [];
try {
    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Compter les écritures
    $stmt = $pdo->query("SELECT COUNT(*) FROM ecritures");
    $stats['total_ecritures'] = $stmt->fetchColumn();
    
    // Compter les comptes
    $stmt = $pdo->query("SELECT COUNT(*) FROM comptes_ohada");
    $stats['total_comptes'] = $stmt->fetchColumn();
    
    // Derniers utilisateurs
    $stmt = $pdo->query("SELECT username, nom_complet, date_derniere_connexion FROM users ORDER BY date_derniere_connexion DESC LIMIT 5");
    $stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Système info
    $stats['php_version'] = phpversion();
    $stats['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu';
    
} catch (Exception $e) {
    $stats['error'] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - SYSCO OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1a237e, #3949ab);
            color: white;
            padding: 2rem;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s;
            margin-bottom: 1.5rem;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-check me-2"></i>Admin SYSCO
            </a>
            <div class="navbar-nav ms-auto">
                <a href="../index.php" class="nav-link">
                    <i class="bi bi-house me-1"></i>Tableau de bord
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Administration</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="dashboard.php" class="text-decoration-none">
                                    <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="../utilisateurs.php" class="text-decoration-none">
                                    <i class="bi bi-people me-2"></i>Utilisateurs
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="../exercices.php" class="text-decoration-none">
                                    <i class="bi bi-calendar me-2"></i>Exercices
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="../sauvegarde.php" class="text-decoration-none">
                                    <i class="bi bi-database me-2"></i>Sauvegarde
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="../logs.php" class="text-decoration-none">
                                    <i class="bi bi-journal-text me-2"></i>Logs système
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Info système -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Système</h6>
                        <small class="d-block mb-1">
                            <i class="bi bi-code-slash"></i> PHP <?= phpversion() ?>
                        </small>
                        <small class="d-block">
                            <i class="bi bi-server"></i> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache' ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-md-9">
                <!-- En-tête -->
                <div class="row mb-4">
                    <div class="col">
                        <h2>
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            Tableau de bord d'administration
                        </h2>
                        <p class="text-muted">Gestion complète du système SYSCO-OHADA</p>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <div class="col">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Utilisateurs</h6>
                                        <h2 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-people card-icon text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Écritures</h6>
                                        <h2 class="mb-0"><?= $stats['total_ecritures'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-journal-text card-icon text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2 text-muted">Comptes</h6>
                                        <h2 class="mb-0"><?= $stats['total_comptes'] ?? 0 ?></h2>
                                    </div>
                                    <i class="bi bi-book card-icon text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des derniers utilisateurs -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Dernières connexions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Nom complet</th>
                                        <th>Dernière connexion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stats['recent_users'])): ?>
                                        <?php foreach($stats['recent_users'] as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['nom_complet']) ?></td>
                                            <td>
                                                <?= $user['date_derniere_connexion'] ? 
                                                    date('d/m/Y H:i', strtotime($user['date_derniere_connexion'])) : 
                                                    'Jamais' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                Aucune donnée disponible
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning me-2"></i>
                            Actions rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="../utilisateurs.php" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Nouvel utilisateur
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../sauvegarde.php" class="btn btn-outline-success w-100">
                                    <i class="bi bi-download me-2"></i>
                                    Sauvegarde BD
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../logs.php" class="btn btn-outline-info w-100">
                                    <i class="bi bi-eye me-2"></i>
                                    Voir les logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
