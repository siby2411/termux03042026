<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

// Traitement ajout immobilisation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_asset'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $acquisition_date = $_POST['acquisition_date'];
    $value = $_POST['value'];
    $location = $_POST['location'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    $stmt = $pdo->prepare("INSERT INTO assets (name, type, acquisition_date, value, location, status, notes) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$name, $type, $acquisition_date, $value, $location, $status, $notes]);
    $success = "Immobilisation ajoutée avec succès.";
}

$assets = $pdo->query("SELECT * FROM assets ORDER BY acquisition_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Immobilisations</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>État des immobilisations</h1>

        <h2>Ajouter une immobilisation</h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Nom" required>
            <input type="text" name="type" placeholder="Type (ordinateur, véhicule, etc.)" required>
            <input type="date" name="acquisition_date" required>
            <input type="number" step="0.01" name="value" placeholder="Valeur (FCFA)" required>
            <input type="text" name="location" placeholder="Emplacement">
            <select name="status">
                <option value="opérationnel">Opérationnel</option>
                <option value="en maintenance">En maintenance</option>
                <option value="hors service">Hors service</option>
            </select>
            <textarea name="notes" placeholder="Notes"></textarea>
            <button type="submit" name="add_asset">Ajouter</button>
        </form>

        <h2>Liste des immobilisations</h2>
        <table border="1">
            <tr><th>Nom</th><th>Type</th><th>Date acquisition</th><th>Valeur</th><th>Emplacement</th><th>Statut</th><th>Notes</th></tr>
            <?php foreach($assets as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= $a['type'] ?></td>
                <td><?= $a['acquisition_date'] ?></td>
                <td><?= number_format($a['value'],2) ?> FCFA</td>
                <td><?= htmlspecialchars($a['location']) ?></td>
                <td><?= $a['status'] ?></td>
                <td><?= htmlspecialchars($a['notes']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
