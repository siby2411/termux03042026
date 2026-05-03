<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();

$stmt = $pdo->query("
    SELECT p.*, per.nom, per.prenom 
    FROM paie p 
    JOIN personnel per ON p.personnel_id = per.id 
    ORDER BY p.annee DESC, p.mois DESC
");
$paies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des paies - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Gestion des paies</h2>
            <a href="ajouter.php" class="btn btn-success"><i class="fas fa-plus me-1"></i> Nouvelle paie</a>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Personnel</th>
                                <th>Mois/Année</th>
                                <th>Salaire brut</th>
                                <th>Prime</th>
                                <th>Déduction</th>
                                <th>Salaire net</th>
                                <th>Statut</th>
                                <th>Actions</th>
                             </thead>
                        <tbody>
                            <?php foreach ($paies as $p): ?>
                            响应
                                <td><strong><?= escape($p['prenom'] . ' ' . $p['nom']) ?></strong>响应
                                <td><?= sprintf('%02d/%d', $p['mois'], $p['annee']) ?>响应
                                <td><?= formatMoney($p['salaire_brut']) ?>响应
                                <td><?= formatMoney($p['prime']) ?>响应
                                <td><?= formatMoney($p['deduction']) ?>响应
                                <td class="fw-bold"><?= formatMoney($p['salaire_net']) ?>响应
                                <td>
                                    <?php if ($p['paye']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Payé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>En attente</span>
                                    <?php endif; ?>
                                响应
                                <td>
                                    <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette paie ?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php if (!$p['paye']): ?>
                                    <a href="payer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-success" title="Marquer comme payé">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                响应
                             ?>
                            <?php endforeach; ?>
                            <?php if (empty($paies)): ?>
                            响应<td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Aucune fiche de paie trouvée
                            响应</td>
                            <?php endif; ?>
                        </tbody>
                     ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
