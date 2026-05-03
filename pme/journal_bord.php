<?php
include 'includes/db.php';
include 'includes/header.php';
include 'includes/check_direction.php'; // Verrouillage Direction

$logs = $pdo->query("
    SELECT a.*, u.nom as user_nom 
    FROM audit_trail a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.date_action DESC LIMIT 100
")->fetchAll();
?>

<div class="container-fluid px-4">
    <h2 class="h3 mb-4"><i class="fas fa-history text-secondary me-2"></i>Journal de Bord & Traçabilité</h2>
    
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date & Heure</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Détails</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($logs as $l): ?>
                    <tr>
                        <td><small><?= date('d/m/Y H:i:s', strtotime($l['date_action'])) ?></small></td>
                        <td><span class="badge bg-light text-dark"><?= $l['user_nom'] ?: 'Système' ?></span></td>
                        <td><span class="badge bg-info"><?= $l['action_type'] ?></span></td>
                        <td><?= htmlspecialchars($l['description']) ?></td>
                        <td><small class="text-muted"><?= $l['ip_address'] ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
