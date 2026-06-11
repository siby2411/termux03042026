<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO consultations (patient_id, medecin_id, service_id, date_consultation, motif_consultation, type_consultation, statut) VALUES (?, ?, ?, NOW(), ?, ?, 'planifiee')");
    $stmt->execute([$_POST['patient_id'], $_POST['medecin_id'], $_POST['service_id'], $_POST['motif'], $_POST['type']]);
    header('Location: index.php?msg=success');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0"><?php require_once '../../includes/sidebar.php'; ?></div>
        <div class="col-md-10 p-4">
            <div class="card p-4 shadow-sm border-0">
                <h2 class="mb-4"><i class="fas fa-stethoscope"></i> Nouvelle Consultation</h2>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <?php $pats = $pdo->query("SELECT id, nom, prenom FROM patients");
                                foreach($pats as $p) echo "<option value='{$p['id']}'>{$p['nom']} {$p['prenom']}</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service / Département</label>
                            <select name="service_id" class="form-select" required>
                                <?php $serv = $pdo->query("SELECT id, nom_service FROM services");
                                foreach($serv as $s) echo "<option value='{$s['id']}'>{$s['nom_service']}</option>"; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Médecin</label>
                        <select name="medecin_id" class="form-select" required>
                            <?php $docs = $pdo->query("SELECT id, nom FROM users WHERE role='medecin'");
                            foreach($docs as $d) echo "<option value='{$d['id']}'>Dr. {$d['nom']}</option>"; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motif de consultation</label>
                        <textarea name="motif" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type de consultation</label>
                        <select name="type" class="form-select">
                            <option value="normale">Normale</option>
                            <option value="urgence">Urgence</option>
                            <option value="controle">Contrôle</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer la consultation</button>
                    <a href="index.php" class="btn btn-secondary px-4">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
