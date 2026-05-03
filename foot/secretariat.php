<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $type = $_POST['report_type'];
    $data = '';
    switch($type) {
        case 'trainees':
            $stmt = $pdo->query("SELECT * FROM trainees");
            $data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'assets':
            $stmt = $pdo->query("SELECT * FROM assets");
            $data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
        case 'appointments':
            $stmt = $pdo->query("SELECT * FROM appointments WHERE appointment_date > NOW()");
            $data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
    }
    $stmt = $pdo->prepare("INSERT INTO admin_reports (report_type, report_data, generated_by) VALUES (?,?,?)");
    $stmt->execute([$type, $data, $_SESSION['user_id']]);
    $success = "Rapport généré avec succès.";
}

$reports = $pdo->query("SELECT * FROM admin_reports ORDER BY generated_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Secrétariat - Rapports</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Secrétariat : Génération de rapports automatiques</h1>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="post">
            <select name="report_type">
                <option value="trainees">Liste des stagiaires</option>
                <option value="assets">Inventaire immobilisations</option>
                <option value="appointments">Rendez-vous à venir</option>
            </select>
            <button type="submit" name="generate_report">Générer le rapport</button>
        </form>

        <h2>Historique des rapports</h2>
        <table border="1">
            <tr><th>Type</th><th>Données (JSON)</th><th>Généré par</th><th>Date</th></tr>
            <?php foreach($reports as $r): ?>
            <tr>
                <td><?= $r['report_type'] ?></td>
                <td><pre><?= htmlspecialchars(substr($r['report_data'],0,200)) ?>...</pre></td>
                <td><?= $r['generated_by'] ?></td>
                <td><?= $r['generated_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
