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

$patients = $pdo->query("SELECT id, prenom, nom, code_patient_unique FROM patients ORDER BY nom")->fetchAll();
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();
$medecins = $pdo->query("SELECT id, prenom, nom FROM users WHERE role = 'medecin' AND actif = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rdv'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO rendez_vous (patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut)
            VALUES (?, ?, ?, ?, ?, ?, 'programme')
        ");
        $stmt->execute([
            $_POST['patient_id'],
            $_POST['service_id'],
            $_POST['medecin_id'] ?: null,
            $_POST['date_rdv'],
            $_POST['heure_rdv'],
            $_POST['motif']
        ]);
        $message = "Rendez-vous enregistré avec succès";
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
    <title>Nouveau rendez-vous</title>
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
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="../admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="liste.php"><i class="fas fa-calendar"></i> Liste RDV</a></li>
                        <li><a href="form.php" class="active"><i class="fas fa-plus-circle"></i> Nouveau RDV</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-10 p-4">
                <h2 class="mb-4"><i class="fas fa-calendar-plus"></i> Nouveau rendez-vous</h2>
                
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                
                <div class="dashboard-card">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Patient</label>
                                <select name="patient_id" class="form-control" required>
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['prenom'] ?> <?= $p['nom'] ?> (<?= $p['code_patient_unique'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Service</label>
                                <select name="service_id" class="form-control" required>
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Médecin (optionnel)</label>
                                <select name="medecin_id" class="form-control">
                                    <option value="">Non spécifié</option>
                                    <?php foreach ($medecins as $m): ?>
                                    <option value="<?= $m['id'] ?>">Dr. <?= $m['prenom'] ?> <?= $m['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Date</label>
                                <input type="date" name="date_rdv" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Heure</label>
                                <input type="time" name="heure_rdv" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Motif</label>
                            <input type="text" name="motif" class="form-control" required>
                        </div>
                        
                        <button type="submit" name="save_rdv" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
