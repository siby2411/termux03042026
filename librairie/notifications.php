<?php
require_once 'includes/config.php';
require_once 'includes/notifications/notifications.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Mes notifications';
$notif = new NotificationSystem($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['read_all'])) {
        $notif->markAllAsRead($_SESSION['user_id']);
        header('Location: notifications.php');
        exit;
    } elseif (isset($_POST['delete']) && isset($_POST['id'])) {
        $notif->delete($_POST['id'], $_SESSION['user_id']);
        header('Location: notifications.php');
        exit;
    } elseif (isset($_POST['read']) && isset($_POST['id'])) {
        $notif->markAsRead($_POST['id'], $_SESSION['user_id']);
        header('Location: notifications.php');
        exit;
    }
}

$notifications = $notif->getAll($_SESSION['user_id']);

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-bell"></i> Mes notifications</h4>
                <?php if(count($notifications) > 0): ?>
                <form method="POST" class="float-end">
                    <button type="submit" name="read_all" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-double"></i> Tout marquer comme lu
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if(empty($notifications)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-inbox fa-3x"></i>
                        <p class="mt-2">Aucune notification</p>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach($notifications as $notif_item): ?>
                        <div class="list-group-item <?php echo $notif_item['is_read'] ? '' : 'list-group-item-primary'; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-<?php echo $notif_item['type'] == 'stock' ? 'box' : 'info-circle'; ?> fa-2x text-<?php echo $notif_item['type'] == 'stock' ? 'warning' : 'info'; ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notif_item['title']); ?></h6>
                                            <p class="mb-1"><?php echo htmlspecialchars($notif_item['message']); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif_item['created_at'])); ?>
                                            </small>
                                            <?php if($notif_item['link']): ?>
                                            <br>
                                            <a href="<?php echo $notif_item['link']; ?>" class="btn btn-sm btn-link p-0 mt-1">
                                                Voir le détail <i class="fas fa-arrow-right"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <?php if(!$notif_item['is_read']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="read" value="1">
                                        <input type="hidden" name="id" value="<?php echo $notif_item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete" value="1">
                                        <input type="hidden" name="id" value="<?php echo $notif_item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette notification ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
