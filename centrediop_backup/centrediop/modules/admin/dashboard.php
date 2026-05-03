<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Vérifier que l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /index.php');
    exit();
}

$stats = getDashboardStats();
$recent_patients = getRecentPatients(10);
$services = getServices();
$queue = getAllQueue();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Centre de Santé Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 1rem;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: #34495e;
            padding-left: 1.5rem;
        }
        .sidebar .nav-link.active {
            background: #3498db;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center text-white mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre de Santé</h5>
                        <small>Mamadou Diop</small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/modules/admin/dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/patients/index.php">
                                <i class="fas fa-users"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/consultations/index.php">
                                <i class="fas fa-stethoscope"></i> Consultations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/queue/index.php">
                                <i class="fas fa-clock"></i> File d'attente
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/payments/index.php">
                                <i class="fas fa-money-bill"></i> Paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/users/index.php">
                                <i class="fas fa-user-cog"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/modules/services/index.php">
                                <i class="fas fa-building"></i> Services
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-danger" href="/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tableau de bord Administrateur</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="me-3"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Patients aujourd'hui</h6>
                                        <h2 class="mb-0"><?= $stats['today_patients'] ?? 0 ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">En attente</h6>
                                        <h2 class="mb-0"><?= $stats['waiting'] ?? 0 ?></h2>
                                    </div>
                                    <i class="fas fa-clock fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Consultations</h6>
                                        <h2 class="mb-0"><?= $stats['today_consultations'] ?? 0 ?></h2>
                                    </div>
                                    <i class="fas fa-stethoscope fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Services</h6>
                                        <h2 class="mb-0"><?= count($services) ?></h2>
                                    </div>
                                    <i class="fas fa-hospital fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File d'attente -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clock"></i> File d'attente en cours</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th>Priorité</th>
                                    <th>Heure d'arrivée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queue as $item): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($item['token']) ?></strong></td>
                                    <td><?= htmlspecialchars($item['patient_name']) ?></td>
                                    <td><?= htmlspecialchars($item['service_name']) ?></td>
                                    <td>
                                        <span class="badge <?= $item['priority'] == 'senior' ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $item['priority'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('H:i', strtotime($item['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Patients récents -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-plus"></i> Patients récents</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Date naissance</th>
                                    <th>Téléphone</th>
                                    <th>Genre</th>
                                    <th>Date inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_patients as $patient): ?>
                                <tr>
                                    <td><?= $patient['id'] ?></td>
                                    <td><?= htmlspecialchars($patient['name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($patient['birthdate'])) ?></td>
                                    <td><?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></td>
                                    <td><?= $patient['gender'] ?? 'N/A' ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($patient['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
