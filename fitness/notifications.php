<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Envoyer une notification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send'])) {
    $query = "INSERT INTO notifications (titre, message, type, destinataire) VALUES (:titre, :msg, :type, :dest)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':titre' => $_POST['titre'],
        ':msg' => $_POST['message'],
        ':type' => $_POST['type'],
        ':dest' => $_POST['destinataire']
    ]);
    $success = "Notification envoyée avec succès!";
}

// Récupérer les adhérents
$adherents = $db->query("SELECT id, nom, prenom FROM adherents WHERE statut='actif'")->fetchAll();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bell"></i> Centre de notifications</h3>
        </div>
        <div class="card-body">
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Titre de la notification</label>
                        <input type="text" name="titre" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control">
                            <option value="info">Information</option>
                            <option value="rappel">Rappel de séance</option>
                            <option value="promo">Promotion</option>
                            <option value="alerte">Alerte</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Destinataire</label>
                        <select name="destinataire" class="form-control">
                            <option value="tous">Tous les adhérents</option>
                            <?php foreach($adherents as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="send" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Envoyer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
