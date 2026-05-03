<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_recruitment'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $position = $_POST['position'];
        $posted = $_POST['posted_date'];
        $expiry = $_POST['expiry_date'];
        $stmt = $pdo->prepare("INSERT INTO recruitments (title, description, position, posted_date, expiry_date) VALUES (?,?,?,?,?)");
        $stmt->execute([$title, $desc, $position, $posted, $expiry]);
    } elseif (isset($_POST['apply'])) {
        $rec_id = $_POST['recruitment_id'];
        $name = $_POST['candidate_name'];
        $email = $_POST['candidate_email'];
        $phone = $_POST['candidate_phone'];
        // Gestion du CV (upload simple, sans fichier réel pour l'exemple)
        $cv = '';
        $stmt = $pdo->prepare("INSERT INTO applications (recruitment_id, candidate_name, candidate_email, candidate_phone, cv_file, applied_date) VALUES (?,?,?,?,?, CURDATE())");
        $stmt->execute([$rec_id, $name, $email, $phone, $cv]);
    }
}

$recruitments = $pdo->query("SELECT * FROM recruitments ORDER BY posted_date DESC")->fetchAll();
$applications = $pdo->query("SELECT a.*, r.title as rec_title FROM applications a JOIN recruitments r ON a.recruitment_id = r.id ORDER BY applied_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recrutement</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Gestion du recrutement</h1>
        <h2>Ajouter une offre</h2>
        <form method="post">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="text" name="position" placeholder="Poste">
            <input type="date" name="posted_date" required>
            <input type="date" name="expiry_date">
            <button type="submit" name="add_recruitment">Ajouter</button>
        </form>

        <h2>Offres en cours</h2>
        <?php foreach($recruitments as $r): ?>
            <div class="rec-card">
                <h3><?= htmlspecialchars($r['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($r['description'])) ?></p>
                <p><strong>Poste :</strong> <?= $r['position'] ?></p>
                <p><strong>Date limite :</strong> <?= $r['expiry_date'] ?></p>
                <details>
                    <summary>Postuler</summary>
                    <form method="post">
                        <input type="hidden" name="recruitment_id" value="<?= $r['id'] ?>">
                        <input type="text" name="candidate_name" placeholder="Nom complet" required>
                        <input type="email" name="candidate_email" placeholder="Email" required>
                        <input type="text" name="candidate_phone" placeholder="Téléphone">
                        <button type="submit" name="apply">Envoyer candidature</button>
                    </form>
                </details>
            </div>
        <?php endforeach; ?>

        <h2>Candidatures reçues</h2>
        <table border="1">
            <tr><th>Offre</th><th>Candidat</th><th>Email</th><th>Téléphone</th><th>Date</th><th>Statut</th></tr>
            <?php foreach($applications as $app): ?>
            <tr>
                <td><?= htmlspecialchars($app['rec_title']) ?></td>
                <td><?= htmlspecialchars($app['candidate_name']) ?></td>
                <td><?= htmlspecialchars($app['candidate_email']) ?></td>
                <td><?= htmlspecialchars($app['candidate_phone']) ?></td>
                <td><?= $app['applied_date'] ?></td>
                <td><?= $app['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
