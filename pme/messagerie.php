<?php
include 'includes/db.php';
include 'includes/header.php';

$mon_service = $_SESSION['service_id'];
$mon_id = $_SESSION['user_id'];

// Envoi d'un message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['envoyer'])) {
    $dest = $_POST['service_dest_id'];
    $txt = $_POST['contenu'];
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, service_dest_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([$mon_id, $dest, $txt]);
    echo "<div class='alert alert-success'>Message envoyé avec succès.</div>";
}

// Récupération des messages reçus pour mon service
$messages = $pdo->prepare("
    SELECT m.*, u.nom as expediteur_nom, s.nom_service as de_service 
    FROM messages m 
    JOIN users u ON m.expediteur_id = u.id 
    JOIN services s ON u.service_id = s.id
    WHERE m.service_dest_id = ? 
    ORDER BY m.date_envoi DESC
");
$messages->execute([$mon_service]);
$recus = $messages->fetchAll();

$services = $pdo->query("SELECT * FROM services")->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-inbox me-2"></i>Messages pour <?= $_SESSION['service_nom'] ?></div>
                <div class="card-body" style="height: 500px; overflow-y: auto;">
                    <?php foreach($recus as $msg): ?>
                    <div class="mb-3 p-3 rounded <?= $msg['lu'] ? 'bg-light' : 'bg-aliceblue border-start border-primary border-4' ?>" style="background-color: #f0f7ff;">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><?= $msg['expediteur_nom'] ?> (<?= $msg['de_service'] ?>)</span>
                            <small class="text-muted"><?= date('d/m H:i', strtotime($msg['date_envoi'])) ?></small>
                        </div>
                        <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Nouveau Message Inter-Service</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Service Destinataire</label>
                            <select name="service_dest_id" class="form-select" required>
                                <?php foreach($services as $s): ?>
                                    <?php if($s['id'] != $mon_service): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['nom_service'] ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Votre message</label>
                            <textarea name="contenu" class="form-control" rows="5" placeholder="Indiquez le numéro de commande ou l'urgence..." required></textarea>
                        </div>
                        <button type="submit" name="envoyer" class="btn btn-primary w-100">Envoyer l'alerte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
