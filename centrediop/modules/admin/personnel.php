<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$personnel = $pdo->query("
    SELECT u.*, s.name as service_nom
    FROM users u
    LEFT JOIN services s ON u.service_id = s.id
    ORDER BY u.role, u.nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du personnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
<?php include $_SERVER["DOCUMENT_ROOT"] . "/includes/sidebar.php"; ?>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-md"></i> Gestion du personnel</h2>
                    <a href="personnel_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau personnel
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Rôle</th>
                                <th>Service</th>
                                <th>Téléphone</th>
                                <th>Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= $p['nom'] ?></td>
                                <td><?= $p['prenom'] ?></td>
                                <td><?= $p['role'] ?></td>
                                <td><?= $p['service_nom'] ?? '-' ?></td>
                                <td><?= $p['telephone'] ?></td>
                                <td><?= $p['username'] ?></td>
                                <td>
                                    <a href="personnel_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
