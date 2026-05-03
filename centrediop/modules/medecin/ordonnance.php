<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$patient_id = $_GET['patient_id'] ?? null;

if (!$patient_id) {
    $patient_token = getPatientFromToken();
    $patient_id = $patient_token['id'] ?? null;
}

if (!$patient_id) {
    header('Location: dashboard.php');
    exit();
}

// Récupérer les infos du patient
$stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: dashboard.php');
    exit();
}

// Traitement du formulaire d'ordonnance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicament = $_POST['medicament'] ?? '';
    $posologie = $_POST['posologie'] ?? '';
    $duree = $_POST['duree'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    
    $insert = $db->prepare("
        INSERT INTO traitements (patient_id, medicament, posologie, duree, date_prescription, medecin_prescripteur, instructions)
        VALUES (?, ?, ?, ?, CURDATE(), ?, ?)
    ");
    
    $insert->execute([
        $patient_id,
        $medicament,
        $posologie,
        $duree,
        'Dr. ' . $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'],
        $instructions
    ]);
    
    $_SESSION['success'] = "Ordonnance enregistrée";
    header('Location: dossier.php?patient_id=' . $patient_id);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ordonnance - <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Nouvelle ordonnance pour <?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h2>
        <form method="POST">
            <div class="mb-3">
                <label>Médicament</label>
                <input type="text" name="medicament" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Posologie</label>
                <input type="text" name="posologie" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Durée</label>
                <input type="text" name="duree" class="form-control" placeholder="Ex: 5 jours">
            </div>
            <div class="mb-3">
                <label>Instructions</label>
                <textarea name="instructions" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="dossier.php?patient_id=<?= $patient_id ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>
