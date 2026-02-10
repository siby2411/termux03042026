<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Récupérer les logs d'audit
$query = "SELECT al.*, p.nom, p.prenom 
          FROM audit_logs al 
          LEFT JOIN personnel p ON al.user_id = p.id 
          ORDER BY al.created_at DESC 
          LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-clock-history me-2"></i>Journal d'Audit
                </h2>
                <p class="text-muted mb-0">Historique des modifications du système</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date/Heure</th>
                                <th>Table</th>
                                <th>Action</th>
                                <th>Utilisateur</th>
                                <th>Anciennes Valeurs</th>
                                <th>Nouvelles Valeurs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4">
                                    <small><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($log['table_name']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $action_colors = [
                                        'INSERT' => 'success',
                                        'UPDATE' => 'warning', 
                                        'DELETE' => 'danger'
                                    ];
                                    $color = $action_colors[$log['action']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($log['action']); ?></span>
                                </td>
                                <td>
                                    <?php if ($log['nom']): ?>
                                        <?php echo htmlspecialchars($log['prenom'] . ' ' . $log['nom']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Système</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['old_values']): ?>
                                        <button class="btn btn-sm btn-outline-info" type="button" 
                                                data-bs-toggle="popover" 
                                                data-bs-content="<?php echo htmlspecialchars($log['old_values']); ?>">
                                            Voir
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['new_values']): ?>
                                        <button class="btn btn-sm btn-outline-success" type="button" 
                                                data-bs-toggle="popover" 
                                                data-bs-content="<?php echo htmlspecialchars($log['new_values']); ?>">
                                            Voir
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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
</div>

<script>
// Activer les popovers Bootstrap
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
})
</script>

<?php include '../../includes/footer.php'; ?>
