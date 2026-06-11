<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$pdo = getPDO();
$patient_id = $_GET['patient_id'] ?? null;

// Récupérer les infos du patient pour l'affichage
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
        $_SESSION['user_id'],
        $_POST['service_id'],
        $_POST['motif'],
        $_POST['diagnostic'],
        $_POST['traitement'],
        $_POST['observations']
    ]);
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Consultation</title>
</head>
<body class="p-4">
    <div class="container">
        <h2>Consultation : <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h2>
        <form method="POST">
            <input type="hidden" name="service_id" value="<?= $_SESSION['user_service_id'] ?? 1 ?>">
            <div class="mb-3"><label>Motif</label><textarea name="motif" class="form-control" required></textarea></div>
            <div class="mb-3"><label>Diagnostic</label><textarea name="diagnostic" class="form-control" required></textarea></div>
            <div class="mb-3"><label>Traitement prescrit</label><textarea name="traitement" class="form-control"></textarea></div>
            <div class="mb-3"><label>Observations</label><textarea name="observations" class="form-control"></textarea></div>
            <button type="submit" class="btn btn-primary">Enregistrer la consultation</button>
            <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>
