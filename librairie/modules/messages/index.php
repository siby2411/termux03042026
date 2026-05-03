<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Messagerie interne';
$action = $_GET['action'] ?? 'inbox';
$success = '';
$error = '';

// Créer la table des messages
$pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        is_read TINYINT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES utilisateurs(id),
        FOREIGN KEY (receiver_id) REFERENCES utilisateurs(id)
    )
");

if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$_GET['read'], $_SESSION['user_id']]);
    header('Location: index.php?action=view&id=' . $_GET['read']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send'])) {
    $receiver_id = $_POST['receiver_id'];
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $subject, $content]);
    $success = "Message envoyé avec succès";
}

if ($action == 'inbox') {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_name, u.nom as sender_nom, u.prenom as sender_prenom
        FROM messages m
        JOIN utilisateurs u ON m.sender_id = u.id
        WHERE m.receiver_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
} elseif ($action == 'sent') {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as receiver_name, u.nom as receiver_nom, u.prenom as receiver_prenom
        FROM messages m
        JOIN utilisateurs u ON m.receiver_id = u.id
        WHERE m.sender_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll();
} elseif ($action == 'view' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               s.username as sender_name, s.nom as sender_nom, s.prenom as sender_prenom,
               r.username as receiver_name, r.nom as receiver_nom, r.prenom as receiver_prenom
        FROM messages m
        JOIN utilisateurs s ON m.sender_id = s.id
        JOIN utilisateurs r ON m.receiver_id = r.id
        WHERE m.id = ? AND (m.sender_id = ? OR m.receiver_id = ?)
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $message = $stmt->fetch();
}

$users = $pdo->prepare("SELECT id, username, nom, prenom FROM utilisateurs WHERE id != ?");
$users->execute([$_SESSION['user_id']]);
$users_list = $users->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">Messagerie</div>
            <div class="list-group list-group-flush">
                <a href="?action=inbox" class="list-group-item <?php echo $action == 'inbox' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i> Boîte de réception
                </a>
                <a href="?action=sent" class="list-group-item <?php echo $action == 'sent' ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i> Envoyés
                </a>
                <a href="?action=compose" class="list-group-item <?php echo $action == 'compose' ? 'active' : ''; ?>">
                    <i class="fas fa-plus"></i> Nouveau message
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><?php echo $action == 'compose' ? 'Nouveau message' : ($action == 'view' ? 'Message' : 'Messages'); ?></div>
            <div class="card-body">
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if($action == 'compose'): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Destinataire</label>
                            <select name="receiver_id" class="form-control" required>
                                <option value="">Sélectionner</option>
                                <?php foreach($users_list as $user): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo $user['prenom'] . ' ' . $user['nom']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Sujet</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Message</label>
                            <textarea name="content" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" name="send" class="btn btn-primary">Envoyer</button>
                    </form>
                    
                <?php elseif($action == 'view' && isset($message)): ?>
                    <h5><?php echo htmlspecialchars($message['subject']); ?></h5>
                    <hr>
                    <p><strong>De:</strong> <?php echo $message['sender_prenom'] . ' ' . $message['sender_nom']; ?></p>
                    <p><strong>À:</strong> <?php echo $message['receiver_prenom'] . ' ' . $message['receiver_nom']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></p>
                    <hr>
                    <div class="bg-light p-3 rounded"><?php echo nl2br(htmlspecialchars($message['content'])); ?></div>
                    <div class="mt-3">
                        <a href="?action=inbox" class="btn btn-secondary">Retour</a>
                    </div>
                    
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Sujet</th><th><?php echo $action == 'inbox' ? 'De' : 'À'; ?></th><th>Date</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($messages as $msg): ?>
                                <tr class="<?php echo ($action == 'inbox' && $msg['is_read'] == 0) ? 'table-primary' : ''; ?>">
                                    <td><a href="?action=view&id=<?php echo $msg['id']; ?>"><?php echo htmlspecialchars($msg['subject']); ?></a></td>
                                    <td><?php echo $action == 'inbox' ? $msg['sender_prenom'] . ' ' . $msg['sender_nom'] : $msg['receiver_prenom'] . ' ' . $msg['receiver_nom']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                                    <td><a href="?delete=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
