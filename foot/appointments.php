<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appointment'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['appointment_date'];
    $location = $_POST['location'];
    $with = $_POST['with_person'];
    $stmt = $pdo->prepare("INSERT INTO appointments (title, description, appointment_date, location, with_person, created_by) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$title, $desc, $date, $location, $with, $_SESSION['user_id']]);
}

$appointments = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rendez-vous Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Planification des rendez-vous</h1>
        <form method="post">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="datetime-local" name="appointment_date" required>
            <input type="text" name="location" placeholder="Lieu">
            <input type="text" name="with_person" placeholder="Avec qui">
            <button type="submit" name="add_appointment">Ajouter</button>
        </form>
        <table border="1">
            <tr><th>Titre</th><th>Description</th><th>Date</th><th>Lieu</th><th>Avec</th></tr>
            <?php foreach($appointments as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['title']) ?></td>
                <td><?= htmlspecialchars($a['description']) ?></td>
                <td><?= $a['appointment_date'] ?></td>
                <td><?= htmlspecialchars($a['location']) ?></td>
                <td><?= htmlspecialchars($a['with_person']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
