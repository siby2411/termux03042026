<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

if (!isset($_GET['patient_id'])) {
    header('Location: ../dashboard/index.php');
    exit;
}

$patientId = $_GET['patient_id'];
$patient = getPatientDetails($pdo, $patientId);

if (!$patient) {
    die("Patient non trouvé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $nextAppointment = $_POST['next_appointment'] ?? null;

    // Enregistrer la consultation
    saveConsultation($pdo, $patientId, $_SESSION['user_id'], $patient['service_id'], $diagnosis, $treatment, $nextAppointment);

    header("Location: ../dashboard/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un dossier médical</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../../assets/images/banniere-omega.png" alt="Omega Informatique">
        <h1>Créer un dossier médical</h1>
    </header>
    <nav>
        <a href="../dashboard/index.php">Retour au dashboard</a>
    </nav>
    <main>
        <form method="post">
            <div class="form-group">
                <label for="patient_id">ID Patient :</label>
                <input type="text" id="patient_id" value="<?= htmlspecialchars($patient['id']) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" id="name" value="<?= htmlspecialchars($patient['name']) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="age">Âge :</label>
                <input type="text" id="age" value="<?= htmlspecialchars($patient['age']) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="text" id="phone" value="<?= htmlspecialchars($patient['phone']) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="diagnosis">Diagnostic :</label>
                <textarea id="diagnosis" name="diagnosis" required></textarea>
            </div>
            <div class="form-group">
                <label for="treatment">Traitement :</label>
                <textarea id="treatment" name="treatment" required></textarea>
            </div>
            <div class="form-group">
                <label for="next_appointment">Prochain rendez-vous :</label>
                <input type="datetime-local" id="next_appointment" name="next_appointment">
            </div>
            <button type="submit">Enregistrer</button>
        </form>
    </main>
</body>
</html>
