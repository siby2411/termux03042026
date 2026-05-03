<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['medecin', 'sagefemme'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();

$patient = null;
$message = '';
$error = '';

// Recherche de patient par code
if (isset($_GET['code'])) {
    $stmt = $pdo->prepare("
        SELECT p.*, d.*
        FROM patients p
        LEFT JOIN dossiers_medicaux d ON p.id = d.patient_id
        WHERE p.code_patient_unique = ? OR p.id = ?
    ");
    $stmt->execute([$_GET['code'], $_GET['code']]);
    $patient = $stmt->fetch();
}

// Enregistrement de la consultation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consultation'])) {
    try {
        $pdo->beginTransaction();
        
        // Mettre à jour le dossier médical si nécessaire
        if (!empty($_POST['antecedents'])) {
            $stmt = $pdo->prepare("
                UPDATE dossiers_medicaux 
                SET antecedents_familiaux = ?,
                    allergies_connues = ?,
                    traitement_long_cours = ?
                WHERE patient_id = ?
            ");
            $stmt->execute([
                $_POST['antecedents'],
                $_POST['allergies'],
                $_POST['traitements'],
                $_POST['patient_id']
            ]);
        }
        
        // Enregistrer la consultation
        $numero_consult = 'CONS-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO consultations (
                numero_consultation, patient_id, medecin_id, service_id,
                date_consultation, motif_consultation, diagnostic, observations, statut
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 'terminee')
        ");
        $stmt->execute([
            $numero_consult,
            $_POST['patient_id'],
            $_SESSION['user_id'],
            $_SESSION['user_service'],
            $_POST['motif'],
            $_POST['diagnostic'],
            $_POST['observations']
        ]);
        
        $consultation_id = $pdo->lastInsertId();
        
        // Enregistrer le prochain rendez-vous si demandé
        if (!empty($_POST['prochain_rdv'])) {
            $stmt = $pdo->prepare("
                INSERT INTO rendez_vous (
                    patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut
                ) VALUES (?, ?, ?, ?, ?, ?, 'programme')
            ");
            $stmt->execute([
                $_POST['patient_id'],
                $_SESSION['user_service'],
                $_SESSION['user_id'],
                $_POST['prochain_rdv'],
                '09:00:00',
                'Consultation de suivi'
            ]);
        }
        
        $pdo->commit();
        $message = "Consultation enregistrée avec succès";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation médicale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x mb-2"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small><?= ucfirst($_SESSION['user_role']) ?></small>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../dashboard/index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="index.php" class="active"><i class="fas fa-stethoscope"></i> Consultation</a></li>
                        <li><a href="../rendezvous/index.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-stethoscope"></i> Consultation médicale</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Recherche de patient -->
                <div class="dashboard-card mb-4">
                    <h5 class="mb-3"><i class="fas fa-search"></i> Rechercher un patient</h5>
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="code" class="form-control" 
                                       placeholder="Code patient (ex: PAT-000001)" 
                                       value="<?= $_GET['code'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <?php if ($patient): ?>
                <!-- Formulaire de consultation -->
                <div class="dashboard-card">
                    <h5 class="mb-3"><i class="fas fa-user"></i> Patient: <?= $patient['prenom'] ?> <?= $patient['nom'] ?></h5>
                    
                    <form method="POST">
                        <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="fw-bold">Code patient</label>
                                <p class="text-primary"><?= $patient['code_patient_unique'] ?></p>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Téléphone</label>
                                <p><?= $patient['telephone'] ?></p>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Date naissance</label>
                                <p><?= date('d/m/Y', strtotime($patient['date_naissance'])) ?></p>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Sexe</label>
                                <p><?= $patient['sexe'] == 'M' ? 'Masculin' : 'Féminin' ?></p>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Dossier médical</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Antécédents médicaux</label>
                                        <textarea name="antecedents" class="form-control" rows="3"><?= $patient['antecedents_familiaux'] ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Allergies connues</label>
                                        <textarea name="allergies" class="form-control" rows="3"><?= $patient['allergies_connues'] ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Traitements en cours</label>
                                        <textarea name="traitements" class="form-control" rows="2"><?= $patient['traitement_long_cours'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Consultation</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label>Motif de consultation</label>
                                    <input type="text" name="motif" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Diagnostic</label>
                                    <textarea name="diagnostic" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Observations</label>
                                    <textarea name="observations" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">Prochain rendez-vous</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Date du prochain rendez-vous</label>
                                        <input type="date" name="prochain_rdv" class="form-control" min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Conseils</label>
                                        <textarea name="conseils" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="save_consultation" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Enregistrer la consultation
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
