<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = $_POST['doctor_id'];
    $action = $_POST['action'];
    markAttendance($pdo, $doctorId, $action);
    header("Location: attendance.php");
    exit;
}

$doctors = getAllDoctors($pdo);
$attendance = getTodayAttendance($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pointage du corps médical</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Pointage du corps médical</h1>
    </header>
    <main>
        <form method="post">
            <div class="form-group">
                <label for="doctor_id">Médecin :</label>
                <select id="doctor_id" name="doctor_id" required>
                    <?php foreach ($doctors as $doctor): ?>
                        <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="action">Action :</label>
                <select id="action" name="action" required>
                    <option value="check_in">Arrivée</option>
                    <option value="check_out">Départ</option>
                </select>
            </div>
            <button type="submit">Enregistrer</button>
        </form>

        <h2>Pointage d'aujourd'hui</h2>
        <table>
            <thead>
                <tr>
                    <th>Médecin</th>
                    <th>Arrivée</th>
                    <th>Départ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                <tr>
                    <td><?= htmlspecialchars($record['doctor_name']) ?></td>
                    <td><?= $record['check_in'] ? date('H:i', strtotime($record['check_in'])) : 'Non' ?></td>
                    <td><?= $record['check_out'] ? date('H:i', strtotime($record['check_out'])) : 'Non' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
