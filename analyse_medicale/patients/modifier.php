<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT p.*, u.* FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient introuvable.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $adresse    = trim($_POST['adresse'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $lieu_naissance = trim($_POST['lieu_naissance'] ?? '');
    $sexe       = $_POST['sexe'] ?? '';
    $groupe_sanguin = $_POST['groupe_sanguin'] ?? '';
    $allergies  = trim($_POST['allergies'] ?? '');
    $antecedent = trim($_POST['antecedent'] ?? '');
    $medecin_traitant = trim($_POST['medecin_traitant'] ?? '');
    $assurance  = trim($_POST['assurance'] ?? '');
    $profession = trim($_POST['profession'] ?? '');

    if (!$first_name || !$last_name || !$date_naissance || !$sexe) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            // Mettre à jour l'utilisateur
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, adresse = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $phone, $adresse, $patient['user_id']]);

            // Mettre à jour le patient
            $stmt = $pdo->prepare("UPDATE patients SET date_naissance = ?, lieu_naissance = ?, sexe = ?, groupe_sanguin = ?, allergies = ?, antecedent_medicaux = ?, medecin_traitant = ?, assurance = ?, profession = ? WHERE id = ?");
            $stmt->execute([$date_naissance, $lieu_naissance, $sexe, $groupe_sanguin, $allergies, $antecedent, $medecin_traitant, $assurance, $profession, $id]);

            $pdo->commit();
            $success = "Patient modifié avec succès.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier patient : <?= escape($patient['last_name'] . ' ' . $patient['first_name']) ?></h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="last_name" class="form-control" value="<?= escape($patient['last_name']) ?>" required></div>
                <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="first_name" class="form-control" value="<?= escape($patient['first_name']) ?>" required></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= escape($patient['email']) ?>"></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="phone" class="form-control" value="<?= escape($patient['phone']) ?>"></div>
                <div class="col-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"><?= escape($patient['adresse']) ?></textarea></div>
                <div class="col-md-6 mb-3"><label>Date de naissance *</label><input type="date" name="date_naissance" class="form-control" value="<?= $patient['date_naissance'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Lieu de naissance</label><input type="text" name="lieu_naissance" class="form-control" value="<?= escape($patient['lieu_naissance']) ?>"></div>
                <div class="col-md-6 mb-3"><label>Sexe *</label><select name="sexe" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="M" <?= $patient['sexe'] == 'M' ? 'selected' : '' ?>>Masculin</option>
                    <option value="F" <?= $patient['sexe'] == 'F' ? 'selected' : '' ?>>Féminin</option>
                </select></div>
                <div class="col-md-6 mb-3"><label>Groupe sanguin</label><select name="groupe_sanguin" class="form-control">
                    <option value="">-- --</option>
                    <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $gs): ?>
                    <option value="<?= $gs ?>" <?= $patient['groupe_sanguin'] == $gs ? 'selected' : '' ?>><?= $gs ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Allergies</label><textarea name="allergies" class="form-control" rows="2"><?= escape($patient['allergies']) ?></textarea></div>
                <div class="col-md-6 mb-3"><label>Antécédents médicaux</label><textarea name="antecedent" class="form-control" rows="2"><?= escape($patient['antecedent_medicaux']) ?></textarea></div>
                <div class="col-md-6 mb-3"><label>Médecin traitant</label><input type="text" name="medecin_traitant" class="form-control" value="<?= escape($patient['medecin_traitant']) ?>"></div>
                <div class="col-md-6 mb-3"><label>Assurance</label><input type="text" name="assurance" class="form-control" value="<?= escape($patient['assurance']) ?>"></div>
                <div class="col-md-6 mb-3"><label>Profession</label><input type="text" name="profession" class="form-control" value="<?= escape($patient['profession']) ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
