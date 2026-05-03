<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $specialty = $_POST['specialty'];
                $phone = $_POST['phone'];
                addDoctor($pdo, $name, $specialty, $phone);
                break;
            case 'update':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $specialty = $_POST['specialty'];
                $phone = $_POST['phone'];
                updateDoctor($pdo, $id, $name, $specialty, $phone);
                break;
            case 'delete':
                $id = $_POST['id'];
                deleteDoctor($pdo, $id);
                break;
        }
        header("Location: manage.php");
        exit;
    }
}

$doctors = getAllDoctors($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion du corps médical</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Gestion du corps médical</h1>
    </header>
    <main>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="specialty">Spécialité :</label>
                <input type="text" id="specialty" name="specialty" required>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <button type="submit">Ajouter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Spécialité</th>
                    <th>Téléphone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?= htmlspecialchars($doctor['id']) ?></td>
                    <td><?= htmlspecialchars($doctor['name']) ?></td>
                    <td><?= htmlspecialchars($doctor['specialty']) ?></td>
                    <td><?= htmlspecialchars($doctor['phone']) ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
