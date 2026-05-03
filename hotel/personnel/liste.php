<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Personnel - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-users me-2"></i>Gestion du personnel</h2>
            <a href="ajouter.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Nouvel employé</a>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                             <tr>
                                <th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Poste</th><th>Salaire base</th><th>Date embauche</th><th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $p): ?>
                             <tr>
                                <td><strong><?= escape($p['nom']) ?></strong></td>
                                <td><?= escape($p['prenom']) ?></td>
                                <td><?= escape($p['telephone']) ?></td>
                                <td><?= escape($p['poste']) ?></td>
                                <td><?= formatMoney($p['salaire_base']) ?></td>
                                <td><?= formatDate($p['date_embauche']) ?></td>
                                <td>
                                    <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet employé ?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                             </tr>
                            <?php endforeach; ?>
                            <?php if (empty($personnel)): ?>
                             <tr><td colspan="7" class="text-center text-muted py-4">Aucun employé enregistré</td></tr>
                            <?php endif; ?>
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
