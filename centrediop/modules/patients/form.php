<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$patient_id = $_GET['id'] ?? null;
$patient = null;
$dossier = null;

if ($patient_id) {
    // Récupérer les informations du patient
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
    
    // Récupérer le dossier médical
    $stmt = $pdo->prepare("SELECT * FROM dossiers_medicaux WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $dossier = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_patient'])) {
    try {
        $pdo->beginTransaction();
        
        if ($patient_id) {
            // Mise à jour du patient
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
            
            // Mise à jour du dossier médical
            if ($dossier) {
                $stmt = $pdo->prepare("
                    UPDATE dossiers_medicaux SET 
                        antecedents_familiaux = ?,
                        allergies_connues = ?,
                        traitement_long_cours = ?
                    WHERE patient_id = ?
                ");
                $stmt->execute([
                    $_POST['antecedents'],
                    $_POST['allergies'],
                    $_POST['traitements'],
                    $patient_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO dossiers_medicaux (patient_id, antecedents_familiaux, allergies_connues, traitement_long_cours)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $patient_id,
                    $_POST['antecedents'],
                    $_POST['allergies'],
                    $_POST['traitements']
                ]);
            }
            
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
            
            $patient_id = $pdo->lastInsertId();
            
            // Créer le dossier médical
            $stmt = $pdo->prepare("
                INSERT INTO dossiers_medicaux (patient_id, antecedents_familiaux, allergies_connues, traitement_long_cours)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $patient_id,
                $_POST['antecedents'],
                $_POST['allergies'],
                $_POST['traitements']
            ]);
            
            $message = "Patient créé avec succès. Numéro: $numero";
        }
        
        // Créer un rendez-vous si demandé
        if (!empty($_POST['prochain_rdv'])) {
            $stmt = $pdo->prepare("
                INSERT INTO rendez_vous (patient_id, service_id, date_rdv, heure_rdv, motif, statut)
                VALUES (?, ?, ?, '09:00:00', 'Consultation de suivi', 'programme')
            ");
            $stmt->execute([
                $patient_id,
                $_POST['service_rdv'] ?: 1,
                $_POST['prochain_rdv']
            ]);
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer les services pour le rendez-vous
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
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
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../<?= $_SESSION['user_role'] ?>/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="form.php" class="active"><i class="fas fa-user-plus"></i> <?= $patient_id ? 'Modifier' : 'Nouveau' ?> patient</a></li>
                        <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="../consultation/form.php"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-user"></i> <?= $patient_id ? 'Modifier' : 'Nouveau' ?> patient</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?></div>
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
                        
                        <h5 class="mt-4 mb-3">📋 Dossier médical</h5>
                        
                        <div class="mb-3">
                            <label>Antécédents médicaux</label>
                            <textarea name="antecedents" class="form-control" rows="3"><?= $dossier['antecedents_familiaux'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Allergies connues</label>
                            <textarea name="allergies" class="form-control" rows="2"><?= $dossier['allergies_connues'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label>Traitements en cours</label>
                            <textarea name="traitements" class="form-control" rows="2"><?= $dossier['traitement_long_cours'] ?? '' ?></textarea>
                        </div>
                        
                        <h5 class="mt-4 mb-3">📅 Prochain rendez-vous (optionnel)</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Date du rendez-vous</label>
                                <input type="date" name="prochain_rdv" class="form-control" min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Service</label>
                                <select name="service_rdv" class="form-control">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="save_patient" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <a href="liste.php" class="btn btn-secondary btn-lg">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
