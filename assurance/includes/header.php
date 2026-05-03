<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="OMEGA Assurance - Système de gestion d'assurance - Conforme aux normes internationales IFRS 17 et Solvabilité II">
    <meta name="theme-color" content="#667eea">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>OMEGA Assurance</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --omega-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--omega-gradient);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--success-color);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
        }
        
        .navbar-brand small {
            font-size: 0.7rem;
            display: block;
            color: rgba(255,255,255,0.9);
        }
        
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: #2c3e50;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background: var(--omega-gradient);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: var(--omega-gradient);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 20px;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: var(--omega-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
        }
        
        .btn-gradient {
            background: var(--omega-gradient);
            color: white;
            border: none;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 3rem;
            color: var(--primary-color);
        }
        
        .footer {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 20px;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                z-index: 1000;
            }
            
            .sidebar.active {
                left: 0;
            }
        }
        
        .alert-custom {
            border-left: 4px solid var(--success-color);
            background: white;
        }
        
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--omega-gradient);
            color: white !important;
        }
        
        .omega-logo-text {
            background: linear-gradient(135deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>

<?php if(!isset($no_navbar) || !$no_navbar): ?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <div>
                    OMEGA ASSURANCE
                    <small>OMEGA Informatique CONSULTING</small>
                </div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <?php if(isset($_SESSION['user_id']) && (!isset($no_sidebar) || !$no_sidebar)): ?>
        <div class="col-md-2 p-0">
            <div class="sidebar">
                <div class="p-3">
                    <div class="text-center mb-4">
                        <div class="bg-gradient p-3 rounded-circle d-inline-block mb-2">
                            <i class="fas fa-chart-line fa-2x text-white"></i>
                        </div>
                        <h6 class="mb-0">OMEGA</h6>
                        <small class="text-muted">Assurance</small>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                        <a class="nav-link" href="clients.php">
                            <i class="fas fa-users"></i> Clients
                        </a>
                        <a class="nav-link" href="vehicules.php">
                            <i class="fas fa-car"></i> Véhicules
                        </a>
                        <a class="nav-link" href="contrats.php">
                            <i class="fas fa-file-signature"></i> Contrats
                        </a>
                        <a class="nav-link" href="sinistres.php">
                            <i class="fas fa-exclamation-triangle"></i> Sinistres
                        </a>
                        <a class="nav-link" href="paiements.php">
                            <i class="fas fa-money-bill-wave"></i> Paiements
                        </a>
                        <a class="nav-link" href="statistiques.php">
                            <i class="fas fa-chart-bar"></i> Statistiques
                        </a>
                        <a class="nav-link" href="etats_financiers.php">
                            <i class="fas fa-chart-pie"></i> États financiers
                        </a>
                        <?php if($_SESSION['user_role'] ?? '' == 'admin'): ?>
                        <hr>
                        <a class="nav-link" href="utilisateurs.php">
                            <i class="fas fa-user-shield"></i> Utilisateurs
                        </a>
                        <a class="nav-link" href="parametres.php">
                            <i class="fas fa-sliders-h"></i> Paramètres
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="position-absolute bottom-0 p-3 w-100">
                    <hr>
                    <small class="text-muted d-block text-center">
                        <i class="fas fa-copyright"></i> OMEGA Consulting<br>
                        Version 1.0
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="main-content">
        <?php else: ?>
        <div class="col-12">
            <div class="main-content">
        <?php endif; ?>
<?php endif; ?>
