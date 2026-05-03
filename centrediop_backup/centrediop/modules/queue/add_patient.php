<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'];
    $serviceId = $_SESSION['service_id'];
    $isSenior = isset($_POST['is_senior']) ? true : false;

    if (addToQueue($pdo, $patientId, $serviceId, $isSenior)) {
        header('Location: list_queue.php?service_id=' . $serviceId);
        exit;
    } else {
        $error = "Erreur lors de l'ajout à la file d'attente.";
    }
}

// Récupérer la liste des patients
$stmt = $pdo->query("SELECT * FROM patients");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un patient à la file</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Ajouter un patient à la file d'attente</h1>
    </header>
    <main>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="patient_id">Patient :</label>
                <select id="patient_id" name="patient_id" required>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['id'] ?>">
                            <?= htmlspecialchars($patient['name']) ?> (<?= $patient['age'] ?> ans)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="checkbox" id="is_senior" name="is_senior">
                <label for="is_senior">Patient du troisième âge</label>
            </div>
            <button type="submit">Ajouter à la file</button>
        </form>
    </main>
</body>
</html>
