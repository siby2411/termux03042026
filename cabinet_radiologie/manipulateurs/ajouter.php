<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $numero_licence = trim($_POST['numero_licence'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (!$first_name || !$last_name || !$qualification || !$numero_licence) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $username = strtolower($first_name . '.' . $last_name);
            $password = password_hash('manip123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'manipulateur')");
            $stmt->execute([$username, $password, $email, $first_name, $last_name, $phone]);
            $userId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO manipulateurs (user_id, qualification, numero_licence, actif) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $qualification, $numero_licence, $actif]);
            $pdo->commit();
            $success = "Manipulateur ajouté. Identifiant : $username / manip123";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter manipulateur</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un manipulateur</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="last_name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="first_name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="phone" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Qualification *</label><input type="text" name="qualification" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Numéro de licence *</label><input type="text" name="numero_licence" class="form-control" required></div>
                <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" name="actif" class="form-check-input" checked> <label>Actif</label></div></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
