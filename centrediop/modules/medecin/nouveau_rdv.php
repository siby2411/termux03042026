<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'medecin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les infos du médecin
$stmt = $db->prepare("SELECT u.*, s.name as service_nom 
                      FROM users u
                      JOIN services s ON u.service_id = s.id
                      WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_rdv'])) {
    try {
        $db->beginTransaction();
        
        // 1. Créer le patient
        $code_patient = 'PAT-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $query_patient = "INSERT INTO patients (
            code_patient_unique, numero_patient, prenom, nom, date_naissance,
            lieu_naissance, sexe, telephone, adresse, created_at
        ) VALUES (
            :code_patient, :code_patient, :prenom, :nom, :date_naissance,
            :lieu_naissance, :sexe, :telephone, :adresse, NOW()
        )";
        
        $stmt_patient = $db->prepare($query_patient);
        $stmt_patient->execute([
            ':code_patient' => $code_patient,
            ':prenom' => $_POST['prenom'],
            ':nom' => $_POST['nom'],
            ':date_naissance' => $_POST['date_naissance'],
            ':lieu_naissance' => $_POST['lieu_naissance'] ?? '',
            ':sexe' => $_POST['sexe'],
            ':telephone' => $_POST['telephone'],
            ':adresse' => $_POST['adresse'] ?? ''
        ]);
        
        $patient_id = $db->lastInsertId();
        
        // 2. Créer le dossier médical
        $query_dossier = "INSERT INTO dossiers_medicaux (
            patient_id, antecedents_familiaux, allergies_connues, 
            traitement_long_cours, created_at
        ) VALUES (
            :patient_id, :antecedents, :allergies, :traitements, NOW()
        )";
        
        $stmt_dossier = $db->prepare($query_dossier);
        $stmt_dossier->execute([
            ':patient_id' => $patient_id,
            ':antecedents' => $_POST['antecedents'] ?? '',
            ':allergies' => $_POST['allergies'] ?? '',
            ':traitements' => $_POST['traitements'] ?? ''
        ]);
        
        // 3. Créer le rendez-vous avec traitement
        $query_rdv = "INSERT INTO rendez_vous (
            patient_id, service_id, medecin_id, date_rdv, heure_rdv, 
            motif, statut, notes, cree_le
        ) VALUES (
            :patient_id, :service_id, :medecin_id, :date_rdv, :heure_rdv,
            :motif, 'programme', :notes, NOW()
        )";
        
        $stmt_rdv = $db->prepare($query_rdv);
        $stmt_rdv->execute([
            ':patient_id' => $patient_id,
            ':service_id' => $medecin['service_id'],
            ':medecin_id' => $_SESSION['user_id'],
            ':date_rdv' => $_POST['date_rdv'],
            ':heure_rdv' => $_POST['heure_rdv'],
            ':motif' => $_POST['motif'] ?? 'Consultation',
            ':notes' => 'Traitement: ' . ($_POST['traitement_type'] ?? 'Non spécifié')
        ]);
        
        $rdv_id = $db->lastInsertId();
        
        $db->commit();
        
        $message = "✅ Patient créé avec succès !";
        $message_type = "success";
        
    } catch (Exception $e) {
        $db->rollBack();
        $message = "❌ Erreur: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Patient et Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px;
            color: white;
        }
        .container { padding: 20px; }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            margin: -25px -25px 20px -25px;
            padding: 15px 25px;
            border-radius: 15px 15px 0 0;
        }
        .btn-save {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: 600;
        }
        .info-rdv {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h3><i class="fas fa-stethoscope"></i> Dr. <?= htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']) ?> - <?= htmlspecialchars($medecin['service_nom']) ?></h3>
            <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus"></i> Nouveau patient et rendez-vous
            </div>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Nom *</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Date naissance *</label>
                        <input type="date" name="date_naissance" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Lieu naissance</label>
                        <input type="text" name="lieu_naissance" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Sexe *</label>
                        <select name="sexe" class="form-select" required>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Téléphone *</label>
                        <input type="tel" name="telephone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Adresse</label>
                        <input type="text" name="adresse" class="form-control">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Antécédents médicaux</label>
                    <textarea name="antecedents" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="mb-3">
                    <label>Allergies</label>
                    <textarea name="allergies" class="form-control" rows="2"></textarea>
                </div>
                
                <hr>
                <h5 class="mb-3"><i class="fas fa-calendar-plus"></i> Rendez-vous et traitement</h5>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Date du rendez-vous *</label>
                        <input type="date" name="date_rdv" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Heure *</label>
                        <input type="time" name="heure_rdv" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Type de traitement</label>
                        <input type="text" name="traitement_type" class="form-control" placeholder="Ex: Extraction dentaire">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Motif de la consultation</label>
                    <textarea name="motif" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="info-rdv">
                    <p class="mb-0"><i class="fas fa-info-circle"></i> Un code patient unique sera généré automatiquement. Le caissier pourra retrouver ce rendez-vous par :</p>
                    <ul class="mb-0 mt-2">
                        <li>Code patient</li>
                        <li>Nom/Prénom</li>
                        <li>Date du rendez-vous</li>
                        <li>Service (<?= htmlspecialchars($medecin['service_nom']) ?>)</li>
                    </ul>
                </div>
                
                <div class="text-end mt-3">
                    <button type="reset" class="btn btn-secondary">Effacer</button>
                    <button type="submit" name="create_rdv" class="btn-save">
                        <i class="fas fa-save"></i> Créer patient et rendez-vous
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
