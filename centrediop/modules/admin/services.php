<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$message = '';
$error = '';

// Ajout d'un service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO services (name, description, couleur, nb_salles)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['couleur'] ?? '#3498db',
            $_POST['nb_salles'] ?? 2
        ]);
        $message = "Service ajouté avec succès";
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Modification d'un service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_service'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE services 
            SET name = ?, description = ?, couleur = ?, nb_salles = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['couleur'],
            $_POST['nb_salles'],
            $_POST['service_id']
        ]);
        $message = "Service modifié avec succès";
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Suppression d'un service
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Service supprimé avec succès";
    } catch (Exception $e) {
        $error = "Impossible de supprimer ce service (il est peut-être utilisé)";
    }
}

// Récupérer tous les services
$services = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM users WHERE service_id = s.id) as nb_personnel,
           (SELECT COUNT(*) FROM consultations WHERE service_id = s.id AND DATE(date_consultation) = CURDATE()) as consultations_ajd
    FROM services s
    ORDER BY s.id
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                        <li><a href="services.php" class="active"><i class="fas fa-building"></i> Services</a></li>
                        <li><a href="prix_services.php"><i class="fas fa-tag"></i> Prix</a></li>
                        <li><a href="consultations.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-building"></i> Gestion des services</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Bouton d'ajout -->
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i> Nouveau service
                </button>
                
                <!-- Liste des services -->
                <div class="dashboard-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Couleur</th>
                                <th>Salles</th>
                                <th>Personnel</th>
                                <th>Consultations ajd</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                            <tr>
                                <td><?= $s['id'] ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?= $s['couleur'] ?>; color: white;">
                                        <?= $s['name'] ?>
                                    </span>
                                </td>
                                <td><?= $s['couleur'] ?></td>
                                <td><?= $s['nb_salles'] ?></td>
                                <td><?= $s['nb_personnel'] ?></td>
                                <td><?= $s['consultations_ajd'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editService(<?= $s['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Supprimer ce service ?')">
                                        <i class="fas fa-trash"></i>
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
    
    <!-- Modal Ajout Service -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Nouveau service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_service" value="1">
                        
                        <div class="mb-3">
                            <label>Nom du service</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Couleur</label>
                            <input type="color" name="couleur" class="form-control" value="#3498db">
                        </div>
                        
                        <div class="mb-3">
                            <label>Nombre de salles</label>
                            <input type="number" name="nb_salles" class="form-control" value="2" min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
