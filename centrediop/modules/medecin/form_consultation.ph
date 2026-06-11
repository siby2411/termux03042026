<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$pdo = getPDO();
$patient_id = $_GET['patient_id'] ?? null;
$message = '';

// Récupérer le patient pour affichage
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO consultations (patient_id, medecin_id, service_id, date_consultation, motif_consultation, diagnostic, traitement_prescrit, observations, statut)
        VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, 'terminee')
    ");
    
    $stmt->execute([
        $patient_id,
        $_SESSION['user_id'], // L'ID du médecin connecté
        $_POST['service_id'],
        $_POST['motif'],
        $_POST['diagnostic'],
        $_POST['traitement'],
        $_POST['observations']
    ]);
    $message = "Consultation enregistrée avec succès.";
}
?>
<div class="container">
    <h2>Consultation pour : <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h2>
    <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>
    <form method="POST">
        <input type="hidden" name="service_id" value="1"> <div class="mb-3"><label>Motif</label><textarea name="motif" class="form-control"></textarea></div>
        <div class="mb-3"><label>Diagnostic</label><textarea name="diagnostic" class="form-control"></textarea></div>
        <div class="mb-3"><label>Traitement prescrit</label><textarea name="traitement" class="form-control"></textarea></div>
        <div class="mb-3"><label>Observations</label><textarea name="observations" class="form-control"></textarea></div>
        <button type="submit" class="btn btn-primary">Valider la consultation</button>
    </form>
</div>
