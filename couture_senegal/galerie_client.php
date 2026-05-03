<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

$client_id = $_GET['id'] ?? null;
if (!$client_id) { header("Location: clients.php"); exit; }

$client = $pdo->query("SELECT * FROM clients WHERE id = $client_id")->fetch();

// Traitement de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = "client_" . $client_id . "_" . time() . "." . $ext;
    $target = "uploads/clients/" . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO client_photos (client_id, image_path, legende) VALUES (?,?,?)");
        $stmt->execute([$client_id, $target, $_POST['legende']]);
        setFlash('success', "Photo ajoutée à la fiche de " . $client['prenom']);
    }
}

$photos = $pdo->query("SELECT * FROM client_photos WHERE client_id = $client_id ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="bi bi-camera me-2 text-gold"></i> Galerie : <?= $client['prenom'] ?> <?= $client['nom'] ?></h4>
    <a href="clients.php" class="btn btn-sm btn-outline-dark">Retour</a>
</div>

<div class="card border-0 shadow-sm mb-4 bg-dark text-white">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="file" name="photo" accept="image/*" capture="environment" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-5">
                <input type="text" name="legende" class="form-control form-control-sm" placeholder="Ex: Tissu Bazin Bleu, Modèle choisi...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-gold w-100" style="background:#d4af37; color:#000; font-weight:bold;">Scanner / Envoyer</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <?php foreach($photos as $p): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <img src="<?= $p['image_path'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Tissu">
            <div class="card-body p-2 text-center">
                <small class="d-block fw-bold"><?= htmlspecialchars($p['legende']) ?></small>
                <small class="text-muted" style="font-size: 10px;"><?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($photos)): ?>
        <div class="col-12 text-center py-5 text-muted">Aucune photo pour ce client. Prenez-en une ! 📸</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
