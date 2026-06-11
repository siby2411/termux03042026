<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$resultats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search = $_POST['search'] ?? '';
    
    $stmt = $db->prepare("
        SELECT * FROM patients 
        WHERE nom LIKE ? 
           OR prenom LIKE ? 
           OR code_patient_unique LIKE ? 
           OR telephone LIKE ?
        ORDER BY nom, prenom
        LIMIT 50
    ");
    $search_term = "%$search%";
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $resultats = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recherche Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between mb-3">
            <h2>Recherche de Patients</h2>
            <a href="dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
        
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Rechercher par nom, prénom, code ou téléphone..." required>
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </form>
        
        <?php if (!empty($resultats)): ?>
            <h4>Résultats (<?= count($resultats) ?>)</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultats as $p): ?>
                    <tr>
                        <td><?= $p['code_patient_unique'] ?></td>
                        <td><?= $p['nom'] ?></td>
                        <td><?= $p['prenom'] ?></td>
                        <td><?= $p['telephone'] ?></td>
                        <td>
                            <a href="consultation.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Consulter</a>
                            <a href="dossier.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Dossier</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-info">Aucun résultat trouvé</div>
        <?php endif; ?>
    </div>
</body>
</html>
