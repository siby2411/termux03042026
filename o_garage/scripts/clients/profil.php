<?php 
require_once '../../includes/header.php'; 
$id = $_GET['id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM clients WHERE id_client = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) { 
    echo "<div class='alert alert-danger'>Client introuvable</div>"; 
    exit; 
}
?>
<div class="container mt-4">
    <div class="card shadow border-0 p-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-circle fa-4x text-primary me-3"></i>
            <div>
                <h2 class="fw-bold mb-0"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></h2>
                <span class="badge bg-info"><?= $c['immatriculation'] ?></span>
            </div>
        </div>
        <hr>
        <p><strong>Téléphone :</strong> <?= $c['telephone'] ?></p>
        <p><strong>Adresse :</strong> <?= $c['adresse'] ?></p>
        <a href="../../index.php" class="btn btn-secondary">Retour au Dashboard</a>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
