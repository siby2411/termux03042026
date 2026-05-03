<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$serviceId = isset($_GET['service_id']) ? $_GET['service_id'] : $_SESSION['service_id'];
$queue = getQueueForService($pdo, $serviceId);
?>

<div class="queue-container">
    <?php if (empty($queue)): ?>
        <p>Aucun patient en attente</p>
    <?php else: ?>
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Patient</th>
                    <th>Téléphone</th>
                    <th>Priorité</th>
                    <th>Heure d'arrivée</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queue as $index => $patient): ?>
                    <tr class="<?= $patient['priority'] == 'senior' ? 'priority-senior' : '' ?>">
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($patient['patient_name']) ?></td>
                        <td><?= htmlspecialchars($patient['phone']) ?></td>
                        <td>
                            <?php if ($patient['priority'] == 'senior'): ?>
                                <span class="badge badge-warning">Sénior</span>
                            <?php else: ?>
                                <span class="badge badge-normal">Normal</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('H:i', strtotime($patient['created_at'])) ?></td>
                        <td>
                            <a href="../consultations/add.php?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-small">Consulter</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.queue-table {
    width: 100%;
    border-collapse: collapse;
}
.queue-table th, .queue-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.queue-table tr:hover {
    background-color: #f5f5f5;
}
.priority-senior {
    background-color: #fff3cd;
}
.badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.9em;
}
.badge-warning {
    background-color: #ffc107;
    color: #000;
}
.badge-normal {
    background-color: #6c757d;
    color: #fff;
}
.btn-small {
    padding: 5px 10px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    font-size: 0.9em;
}
.btn-small:hover {
    background-color: #0056b3;
}
</style>
