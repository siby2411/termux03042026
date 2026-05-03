<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'medecin', 'sagefemme'])) {
    header('Location: /login.php');
    exit();
}

$pdo = getPDO();
$message = '';
$error = '';
$patient_id = $_GET['patient_id'] ?? null;
$file_id = $_GET['file_id'] ?? null;
$patient = null;

if ($patient_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, d.*
        FROM patients p
        LEFT JOIN dossiers_medicaux d ON p.id = d.patient_id
        WHERE p.id = ?
    ");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
}

$actes = $pdo->query("SELECT * FROM actes_medicaux ORDER BY libelle")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consultation'])) {
    try {
        $pdo->beginTransaction();
        
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
            $_SESSION['user_service'] ?? 1,
            $_POST['motif'],
            $_POST['diagnostic'],
            $_POST['observations']
        ]);
        
        $consultation_id = $pdo->lastInsertId();
        
        if (isset($_POST['actes']) && is_array($_POST['actes'])) {
            foreach ($_POST['actes'] as $acte_id) {
                $stmt_acte = $pdo->prepare("SELECT prix_consultation, prix_traitement FROM actes_medicaux WHERE id = ?");
                $stmt_acte->execute([$acte_id]);
                $acte = $stmt_acte->fetch();
                $prix = $acte['prix_consultation'] ?: $acte['prix_traitement'];
                
                $stmt = $pdo->prepare("INSERT INTO consultation_actes (consultation_id, acte_id, prix_applique) VALUES (?, ?, ?)");
                $stmt->execute([$consultation_id, $acte_id, $prix]);
            }
        }
        
        if ($file_id) {
            $pdo->prepare("UPDATE file_attente SET statut = 'termine' WHERE id = ?")->execute([$file_id]);
        }
        
        $pdo->commit();
        $message = "Consultation enregistrée avec succès";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur: " . $e->getMessage();
    }
}

// File d'attente
$queue = $pdo->prepare("
    SELECT f.*, p.prenom, p.nom, p.code_patient_unique
    FROM file_attente f
    JOIN patients p ON f.patient_id = p.id
    WHERE f.service_id = ? AND f.statut = 'en_attente'
    ORDER BY FIELD(f.priorite, 'urgence', 'senior', 'normal'), f.cree_a ASC
");
$queue->execute([$_SESSION['user_service'] ?? 1]);
$waiting = $queue->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle consultation</title>
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
                        <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Patients</a></li>
                        <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="form.php" class="active"><i class="fas fa-plus-circle"></i> Nouvelle consultation</a></li>
                        <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                        <li><a href="../rendezvous/form.php"><i class="fas fa-calendar-plus"></i> Prendre RDV</a></li>
                        <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'caissier'): ?>
                        <li><a href="../paiements/liste.php"><i class="fas fa-credit-card"></i> Paiements</a></li>
                        <li><a href="../paiements/form.php"><i class="fas fa-plus-circle"></i> Nouveau paiement</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                        <?php endif; ?>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-stethoscope"></i> Nouvelle consultation</h2>
                
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <h5 class="mb-3"><i class="fas fa-clock"></i> En attente</h5>
                            <?php foreach ($waiting as $w): ?>
                            <div class="border p-2 mb-2">
                                <strong><?= $w['prenom'] ?> <?= $w['nom'] ?></strong><br>
                                <small><?= $w['code_patient_unique'] ?></small><br>
                                <a href="?patient_id=<?= $w['patient_id'] ?>&file_id=<?= $w['id'] ?>" class="btn btn-sm btn-primary mt-2">
                                    Consulter
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <?php if ($patient): ?>
                        <div class="dashboard-card">
                            <h5 class="mb-3">Patient: <?= $patient['prenom'] ?> <?= $patient['nom'] ?></h5>
                            <form method="POST">
                                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                <input type="hidden" name="file_id" value="<?= $file_id ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">Code: <?= $patient['code_patient_unique'] ?></div>
                                    <div class="col-md-6">Tél: <?= $patient['telephone'] ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Motif</label>
                                    <input type="text" name="motif" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Diagnostic</label>
                                    <textarea name="diagnostic" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Observations</label>
                                    <textarea name="observations" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Actes</label>
                                    <?php foreach ($actes as $a): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="actes[]" value="<?= $a['id'] ?>" class="form-check-input">
                                        <label class="form-check-label"><?= $a['libelle'] ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="submit" name="save_consultation" class="btn btn-primary">
                                    Enregistrer
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">Sélectionnez un patient dans la file d'attente</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
