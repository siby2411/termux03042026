<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$search = $_GET['search'] ?? '';
$patients = [];

if ($search) {
    $stmt = $pdo->prepare("
        SELECT * FROM patients 
        WHERE prenom LIKE ? OR nom LIKE ? OR telephone LIKE ? OR numero_patient LIKE ?
        ORDER BY created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
    $patients = $stmt->fetchAll();
} else {
    $patients = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC LIMIT 50")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des patients</title>
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
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
<?php include $_SERVER["DOCUMENT_ROOT"] . "/includes/sidebar.php"; ?>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users"></i> Liste des patients</h2>
                    <a href="form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau patient
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Rechercher par nom, prénom, téléphone..." value="<?= $search ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                            </div>
                        </div>
                    </form>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N° Patient</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Téléphone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $p): ?>
                            <tr>
                                <td><?= $p['numero_patient'] ?></td>
                                <td><?= $p['nom'] ?></td>
                                <td><?= $p['prenom'] ?></td>
                                <td><?= $p['telephone'] ?></td>
                                <td>
                                    <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../consultation/form.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-stethoscope"></i>
                                    </a>
                                    <a href="../rendezvous/form.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                    <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'caissier'): ?>
                                    <a href="../paiements/form.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                    <?php endif; ?>
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
