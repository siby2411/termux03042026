<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();

$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone
        FROM patients p
        JOIN users u ON p.user_id = u.id
        WHERE u.first_name LIKE :search OR u.last_name LIKE :search OR p.code_patient LIKE :search
        ORDER BY u.last_name, u.first_name";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Laboratoire Médical</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="liste.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="../analyses/liste.php">Analyses</a></li>
                    <li class="nav-item"><a class="nav-link" href="../prelevements/liste.php">Prélèvements</a></li>
                    <li class="nav-item"><a class="nav-link" href="../factures/liste.php">Factures</a></li>
                </ul>
                <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Liste des patients</h2>
        <form method="get" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, prénom ou code patient" value="<?= escape($search) ?>">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a href="ajouter.php" class="btn btn-success">+ Nouveau patient</a>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Code patient</th>
                    <th>Nom complet</th>
                    <th>Sexe</th>
                    <th>Date naissance</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= escape($p['code_patient']) ?></td>
                    <td><?= escape($p['last_name'] . ' ' . $p['first_name']) ?></td>
                    <td><?= escape($p['sexe']) ?></td>
                    <td><?= formatDate($p['date_naissance']) ?></td>
                    <td><?= escape($p['phone']) ?></td>
                    <td>
                        <a href="fiche.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
