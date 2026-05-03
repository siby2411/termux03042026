<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$patient_id = $_GET['id'] ?? null;
$patient = null;

if ($patient_id) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_patient'])) {
            if ($patient_id) {
                // Mise à jour
                $stmt = $pdo->prepare("
                    UPDATE patients SET 
                        prenom = ?, nom = ?, date_naissance = ?, sexe = ?,
                        telephone = ?, adresse = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['prenom'], $_POST['nom'], $_POST['date_naissance'],
                    $_POST['sexe'], $_POST['telephone'], $_POST['adresse'],
                    $patient_id
                ]);
                $message = "Patient modifié avec succès";
            } else {
                // Nouveau patient
                $numero = 'PAT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $stmt = $pdo->prepare("
                    INSERT INTO patients (numero_patient, prenom, nom, date_naissance, sexe, telephone, adresse)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $numero, $_POST['prenom'], $_POST['nom'], $_POST['date_naissance'],
                    $_POST['sexe'], $_POST['telephone'], $_POST['adresse']
                ]);
                
                // Créer dossier médical
                $patient_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO dossiers_medicaux (patient_id) VALUES (?)")->execute([$patient_id]);
                
                $message = "Patient créé avec succès. Numéro: $numero";
            }
        }
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $patient_id ? 'Modifier' : 'Nouveau' ?> patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Administrateur</small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="patients.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="patient_form.php" class="active"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                        <li><a href="personnel_form.php"><i class="fas fa-user-plus"></i> Nouveau personnel</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../paiements/form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <li><a href="../statistiques/index.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-user"></i> <?= $patient_id ? 'Modifier' : 'Nouveau' ?> patient</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="dashboard-card">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Prénom</label>
                                <input type="text" name="prenom" class="form-control" value="<?= $patient['prenom'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= $patient['nom'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Date naissance</label>
                                <input type="date" name="date_naissance" class="form-control" value="<?= $patient['date_naissance'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Sexe</label>
                                <select name="sexe" class="form-control" required>
                                    <option value="M" <?= ($patient['sexe'] ?? '') == 'M' ? 'selected' : '' ?>>Masculin</option>
                                    <option value="F" <?= ($patient['sexe'] ?? '') == 'F' ? 'selected' : '' ?>>Féminin</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="<?= $patient['telephone'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"><?= $patient['adresse'] ?? '' ?></textarea>
                        </div>
                        
                        <button type="submit" name="save_patient" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="patients.php" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
