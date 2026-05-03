<?php
require_once 'includes/config.php';
redirectIfNotLoggedIn();

// Traitement ajout terrain
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_field'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $capacity = $_POST['capacity'];
        $available = isset($_POST['available']) ? 1 : 0;
        $description = $_POST['description'];
        $stmt = $pdo->prepare("INSERT INTO fields (name, type, capacity, available, description) VALUES (?,?,?,?,?)");
        $stmt->execute([$name, $type, $capacity, $available, $description]);
        $success_field = "Terrain ajouté avec succès.";
    }
    if (isset($_POST['add_room'])) {
        $name = $_POST['name'];
        $capacity = $_POST['capacity'];
        $equipment = $_POST['equipment'];
        $available = isset($_POST['available']) ? 1 : 0;
        $stmt = $pdo->prepare("INSERT INTO meeting_rooms (name, capacity, equipment, available) VALUES (?,?,?,?)");
        $stmt->execute([$name, $capacity, $equipment, $available]);
        $success_room = "Salle ajoutée avec succès.";
    }
}

$fields = $pdo->query("SELECT * FROM fields")->fetchAll();
$rooms = $pdo->query("SELECT * FROM meeting_rooms")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terrains & Salles</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <main>
        <h1>Disponibilité des terrains et salles</h1>

        <!-- Formulaire ajout terrain -->
        <h2>Ajouter un terrain</h2>
        <?php if(isset($success_field)) echo "<p class='success'>$success_field</p>"; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Nom du terrain" required>
            <input type="text" name="type" placeholder="Type (gazon, synthétique...)" required>
            <input type="number" name="capacity" placeholder="Capacité" required>
            <label><input type="checkbox" name="available" value="1"> Disponible</label>
            <textarea name="description" placeholder="Description"></textarea>
            <button type="submit" name="add_field">Ajouter</button>
        </form>

        <!-- Formulaire ajout salle -->
        <h2>Ajouter une salle de réunion</h2>
        <?php if(isset($success_room)) echo "<p class='success'>$success_room</p>"; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Nom de la salle" required>
            <input type="number" name="capacity" placeholder="Capacité" required>
            <input type="text" name="equipment" placeholder="Équipement (vidéoprojecteur, etc.)">
            <label><input type="checkbox" name="available" value="1"> Disponible</label>
            <button type="submit" name="add_room">Ajouter</button>
        </form>

        <h2>Liste des terrains</h2>
        <table border="1">
            <tr><th>Nom</th><th>Type</th><th>Capacité</th><th>Disponible</th><th>Description</th></tr>
            <?php foreach($fields as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['name']) ?></td>
                <td><?= $f['type'] ?></td>
                <td><?= $f['capacity'] ?></td>
                <td><?= $f['available'] ? 'Oui' : 'Non' ?></td>
                <td><?= htmlspecialchars($f['description']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Liste des salles de réunion</h2>
        <table border="1">
            <tr><th>Nom</th><th>Capacité</th><th>Équipement</th><th>Disponible</th></tr>
            <?php foreach($rooms as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= $r['capacity'] ?></td>
                <td><?= $r['equipment'] ?></td>
                <td><?= $r['available'] ? 'Oui' : 'Non' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
