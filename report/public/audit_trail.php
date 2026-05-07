<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$page_title = "Audit Trail - Traçabilité des actions";
$page_icon = "eye";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des logs d'audit
$logs = $pdo->query("
    SELECT a.*, u.email as utilisateur 
    FROM AUDIT_TRAIL a
    LEFT JOIN USERS u ON a.utilisateur_id = u.user_id
    ORDER BY a.date_action DESC 
    LIMIT 100
")->fetchAll();

$stats = $pdo->query("
    SELECT action, COUNT(*) as total 
    FROM AUDIT_TRAIL 
    GROUP BY action
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="bi bi-eye"></i> Audit Trail - Journal des actions</h5>
                <small>Traçabilité complète des opérations effectuées</small>
            </div>
            <div class="card-body">
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <?php foreach($stats as $s): ?>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white text-center">
                            <div class="card-body">
                                <i class="bi bi-<?= $s['action'] == 'INSERT' ? 'plus-circle' : ($s['action'] == 'UPDATE' ? 'pencil-square' : ($s['action'] == 'DELETE' ? 'trash' : 'box-arrow-in-right')) ?>"></i>
                                <h5><?= $s['total'] ?></h5>
                                <small><?= $s['action'] ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tableau des logs -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="auditTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>ID enregistrement</th>
                                <th>Anciennes valeurs</th>
                                <th>Nouvelles valeurs</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="text-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['date_action'])) ?></td>
                                <td><?= htmlspecialchars($log['utilisateur'] ?? 'Inconnu') ?></td>
                                <td>
                                    <span class="badge <?= $log['action'] == 'INSERT' ? 'bg-success' : ($log['action'] == 'UPDATE' ? 'bg-warning' : ($log['action'] == 'DELETE' ? 'bg-danger' : 'bg-info')) ?>">
                                        <?= $log['action'] ?>
                                    </span>
                                </td>
                                <td><?= $log['table_concernee'] ?></td>
                                <td class="text-center"><?= $log['record_id'] ?? '-' ?></td>
                                <td><small><?= htmlspecialchars(substr($log['anciennes_valeurs'] ?? '', 0, 50)) ?></small></td>
                                <td><small><?= htmlspecialchars(substr($log['nouvelles_valeurs'] ?? '', 0, 50)) ?></small></td>
                                <td><small><?= $log['ip_adresse'] ?? '-' ?></small></td>
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
$(document).ready(function() {
    $('#auditTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        }
    });
});
</script>

<?php include 'inc_footer.php'; ?>
