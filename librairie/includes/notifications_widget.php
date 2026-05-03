<?php
if (!isset($_SESSION['user_id'])) return;

require_once __DIR__ . '/notifications/notifications.php';
$notif = new NotificationSystem($pdo);
$unread_count = $notif->getCount($_SESSION['user_id']);
$notifications = $notif->getUnread($_SESSION['user_id']);
?>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
        <i class="fas fa-bell"></i>
        <?php if($unread_count > 0): ?>
        <span class="badge bg-danger rounded-pill"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end" style="width: 350px;">
        <div class="dropdown-header d-flex justify-content-between">
            <span>Notifications</span>
            <?php if($unread_count > 0): ?>
            <button class="btn btn-sm btn-link p-0" onclick="markAllRead()">Tout marquer comme lu</button>
            <?php endif; ?>
        </div>
        <div class="dropdown-divider"></div>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php if(empty($notifications)): ?>
            <div class="dropdown-item text-center text-muted">
                <i class="fas fa-check-circle"></i> Aucune notification
            </div>
            <?php else: ?>
                <?php foreach($notifications as $notif_item): ?>
                <div class="dropdown-item notification-item" data-id="<?php echo $notif_item['id']; ?>">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-2">
                            <i class="fas fa-<?php echo $notif_item['type'] == 'stock' ? 'box' : 'info-circle'; ?> text-<?php echo $notif_item['type'] == 'stock' ? 'warning' : 'info'; ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?php echo htmlspecialchars($notif_item['title']); ?></strong>
                            <div class="small"><?php echo htmlspecialchars($notif_item['message']); ?></div>
                            <div class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($notif_item['created_at'])); ?></div>
                        </div>
                        <button class="btn btn-sm btn-link text-danger" onclick="deleteNotif(<?php echo $notif_item['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="dropdown-footer text-center p-2">
            <a href="notifications.php" class="text-decoration-none">Voir toutes les notifications</a>
        </div>
    </div>
</li>

<script>
function markAllRead() {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=read_all'
    }).then(() => location.reload());
}

function deleteNotif(id) {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete&id=' + id
    }).then(() => location.reload());
}

// Polling toutes les 30 secondes
setInterval(() => {
    fetch('api/notifications.php?action=get')
        .then(res => res.json())
        .then(data => {
            if(data.count > 0) {
                const badge = document.querySelector('#notificationsDropdown .badge');
                if(badge) badge.textContent = data.count;
                else {
                    document.querySelector('#notificationsDropdown').innerHTML += 
                        '<span class="badge bg-danger rounded-pill">' + data.count + '</span>';
                }
            }
        });
}, 30000);
</script>
