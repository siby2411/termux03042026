<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

// Traitement ajout rendez-vous
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_appointment'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['appointment_date'];
    $location = $_POST['location'];
    $with = $_POST['with_person'];
    $stmt = $pdo->prepare("INSERT INTO appointments (title, description, appointment_date, location, with_person, created_by) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$title, $desc, $date, $location, $with, $_SESSION['user_id']]);
    $success = "Rendez-vous ajouté avec succès.";
}

$stmt = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date DESC");
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Calendrier des événements</h1>

        <h2>Planifier un rendez-vous</h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="post">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="datetime-local" name="appointment_date" required>
            <input type="text" name="location" placeholder="Lieu">
            <input type="text" name="with_person" placeholder="Avec qui">
            <button type="submit" name="add_appointment">Ajouter</button>
        </form>

        <h2>Liste des rendez-vous</h2>
        <table border="1" cellpadding="10">
            <tr><th>Titre</th><th>Description</th><th>Date & Heure</th><th>Lieu</th><th>Avec</th></tr>
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
