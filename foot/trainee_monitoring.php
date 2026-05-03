<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_progress'])) {
        $trainee_id = $_POST['trainee_id'];
        $eval_date = $_POST['evaluation_date'];
        $physical = $_POST['physical_rating'];
        $technical = $_POST['technical_rating'];
        $tactical = $_POST['tactical_rating'];
        $comment = $_POST['coach_comment'];
        $stmt = $pdo->prepare("INSERT INTO trainee_progress (trainee_id, evaluation_date, physical_rating, technical_rating, tactical_rating, coach_comment) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$trainee_id, $eval_date, $physical, $technical, $tactical, $comment]);
    }
    if (isset($_POST['add_trainee'])) {
        $first = $_POST['first_name'];
        $last = $_POST['last_name'];
        $birth = $_POST['birth_date'];
        $pos = $_POST['position'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $reg_date = $_POST['registration_date'];
        $stmt = $pdo->prepare("INSERT INTO trainees (first_name, last_name, birth_date, position, phone, email, address, registration_date) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$first, $last, $birth, $pos, $phone, $email, $address, $reg_date]);
    }
}

$trainees = $pdo->query("SELECT * FROM trainees ORDER BY registration_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suivi des stagiaires</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Suivi des footballeurs</h1>

        <h2>Ajouter un stagiaire</h2>
        <form method="post">
            <input type="text" name="first_name" placeholder="Prénom" required>
            <input type="text" name="last_name" placeholder="Nom" required>
            <input type="date" name="birth_date">
            <input type="text" name="position" placeholder="Poste">
            <input type="text" name="phone" placeholder="Téléphone">
            <input type="email" name="email" placeholder="Email">
            <textarea name="address" placeholder="Adresse"></textarea>
            <input type="date" name="registration_date" required>
            <button type="submit" name="add_trainee">Ajouter</button>
        </form>

        <h2>Liste des stagiaires</h2>
        <?php foreach($trainees as $t): ?>
            <div class="trainee-card">
                <strong><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></strong> - <?= $t['position'] ?>
                <details>
                    <summary>Ajouter une évaluation</summary>
                    <form method="post">
                        <input type="hidden" name="trainee_id" value="<?= $t['id'] ?>">
                        <input type="date" name="evaluation_date" required>
                        <label>Physique (1-10) : <input type="number" name="physical_rating" min="1" max="10"></label>
                        <label>Technique : <input type="number" name="technical_rating" min="1" max="10"></label>
                        <label>Tactique : <input type="number" name="tactical_rating" min="1" max="10"></label>
                        <textarea name="coach_comment" placeholder="Commentaire du coach"></textarea>
                        <button type="submit" name="add_progress">Enregistrer</button>
                    </form>
                </details>
                <h4>Historique des évaluations</h4>
                <?php
                    $stmt = $pdo->prepare("SELECT * FROM trainee_progress WHERE trainee_id = ? ORDER BY evaluation_date DESC");
                    $stmt->execute([$t['id']]);
                    $progress = $stmt->fetchAll();
                ?>
                <table border="1">
                    <tr><th>Date</th><th>Physique</th><th>Technique</th><th>Tactique</th><th>Commentaire</th></tr>
                    <?php foreach($progress as $p): ?>
                    <tr>
                        <td><?= $p['evaluation_date'] ?></td>
                        <td><?= $p['physical_rating'] ?></td>
                        <td><?= $p['technical_rating'] ?></td>
                        <td><?= $p['tactical_rating'] ?></td>
                        <td><?= htmlspecialchars($p['coach_comment']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>
