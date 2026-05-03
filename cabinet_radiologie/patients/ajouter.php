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
    $adresse = trim($_POST['adresse'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $groupe_sanguin = $_POST['groupe_sanguin'] ?? '';
    $allergies = trim($_POST['allergies'] ?? '');
    $antecedent = trim($_POST['antecedent'] ?? '');
    $medecin_traitant = trim($_POST['medecin_traitant'] ?? '');
    $assurance = trim($_POST['assurance'] ?? '');
    $numero_secu = trim($_POST['numero_secu'] ?? '');

    if (!$first_name || !$last_name || !$date_naissance) {
        $error = "Veuillez remplir les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();
            $username = strtolower($first_name . '.' . $last_name);
            $password = password_hash('patient123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, adresse, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'patient')");
            $stmt->execute([$username, $password, $email, $first_name, $last_name, $phone, $adresse]);
            $userId = $pdo->lastInsertId();
            $last_code = $pdo->query("SELECT code_patient FROM patients ORDER BY id DESC LIMIT 1")->fetchColumn();
            $num = $last_code ? (int)substr($last_code, -4) + 1 : 1;
            $code_patient = "PAT-" . date('Y') . "-" . sprintf("%04d", $num);
            $stmt = $pdo->prepare("INSERT INTO patients (user_id, code_patient, date_naissance, groupe_sanguin, allergies, antecedent_medicaux, medecin_traitant, assurance, numero_secu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $code_patient, $date_naissance, $groupe_sanguin, $allergies, $antecedent, $medecin_traitant, $assurance, $numero_secu]);
            $pdo->commit();
            $success = "Patient ajouté. Identifiant : $username / patient123";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter patient</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un patient</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="last_name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="first_name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="phone" class="form-control"></div>
                <div class="col-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6 mb-3"><label>Date de naissance *</label><input type="date" name="date_naissance" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Groupe sanguin</label><input type="text" name="groupe_sanguin" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Allergies</label><textarea name="allergies" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6 mb-3"><label>Antécédents</label><textarea name="antecedent" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6 mb-3"><label>Médecin traitant</label><input type="text" name="medecin_traitant" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Assurance</label><input type="text" name="assurance" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>N° Sécurité sociale</label><input type="text" name="numero_secu" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
