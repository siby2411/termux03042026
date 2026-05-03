<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = strtolower($first_name . '.' . $last_name);
    $password   = password_hash('patient123', PASSWORD_DEFAULT); // mot de passe par défaut
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

    // Validation minimale
    if (!$first_name || !$last_name || !$date_naissance || !$sexe) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $pdo->beginTransaction();

            // Créer l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, first_name, last_name, phone, adresse, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'patient')");
            $stmt->execute([$username, $password, $email, $first_name, $last_name, $phone, $adresse]);
            $userId = $pdo->lastInsertId();

            // Créer le patient
            $stmt = $pdo->prepare("INSERT INTO patients (user_id, date_naissance, lieu_naissance, sexe, groupe_sanguin, allergies, antecedent_medicaux, medecin_traitant, assurance, profession) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $date_naissance, $lieu_naissance, $sexe, $groupe_sanguin, $allergies, $antecedent, $medecin_traitant, $assurance, $profession]);

            $pdo->commit();
            $success = "Patient ajouté avec succès. Identifiant : $username, mot de passe par défaut : patient123";
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
    <title>Ajouter un patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Laboratoire Médical</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="liste.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="../analyses/liste.php">Analyses</a></li>
                    <li class="nav-item"><a class="nav-link" href="../prelevements/liste.php">Prélèvements</a></li>
                    <li class="nav-item"><a class="nav-link" href="../factures/liste.php">Factures</a></li>
                </ul>
                <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Ajouter un patient</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nom *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Prénom *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Téléphone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label>Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Date de naissance *</label>
                    <input type="date" name="date_naissance" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Sexe *</label>
                    <select name="sexe" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Groupe sanguin</label>
                    <select name="groupe_sanguin" class="form-control">
                        <option value="">-- --</option>
                        <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                        <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Allergies</label>
                    <textarea name="allergies" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Antécédents médicaux</label>
                    <textarea name="antecedent" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Médecin traitant</label>
                    <input type="text" name="medecin_traitant" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Assurance</label>
                    <input type="text" name="assurance" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Profession</label>
                    <input type="text" name="profession" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>
