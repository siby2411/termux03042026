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

$stmt = $pdo->prepare("SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.adresse
                       FROM patients p
                       JOIN users u ON p.user_id = u.id
                       WHERE p.id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche patient - <?= escape($patient['last_name']) ?></title>
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
        <h2>Fiche patient : <?= escape($patient['last_name'] . ' ' . $patient['first_name']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Code patient</th><td><?= escape($patient['code_patient']) ?></td></tr>
                    <tr><th>Nom complet</th><td><?= escape($patient['last_name'] . ' ' . $patient['first_name']) ?></td></tr>
                    <tr><th>Sexe</th><td><?= escape($patient['sexe']) ?></td></tr>
                    <tr><th>Date de naissance</th><td><?= formatDate($patient['date_naissance']) ?></td></tr>
                    <tr><th>Lieu de naissance</th><td><?= escape($patient['lieu_naissance']) ?></td></tr>
                    <tr><th>Groupe sanguin</th><td><?= escape($patient['groupe_sanguin']) ?></td></tr>
                    <tr><th>Allergies</th><td><?= nl2br(escape($patient['allergies'])) ?></td></tr>
                    <tr><th>Antécédents médicaux</th><td><?= nl2br(escape($patient['antecedent_medicaux'])) ?></td></tr>
                    <tr><th>Médecin traitant</th><td><?= escape($patient['medecin_traitant']) ?></td></tr>
                    <tr><th>Assurance</th><td><?= escape($patient['assurance']) ?></td></tr>
                    <tr><th>Profession</th><td><?= escape($patient['profession']) ?></td></tr>
                    <tr><th>Téléphone</th><td><?= escape($patient['phone']) ?></td></tr>
                    <tr><th>Email</th><td><?= escape($patient['email']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= nl2br(escape($patient['adresse'])) ?></td></tr>
                </table>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour à la liste</a>
    </div>
</body>
</html>
