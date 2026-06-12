<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$db = getPDO();

// Requête corrigée avec les bons noms de colonnes : medecin_id et service_id
$query = "SELECT DISTINCT p.*, s.name as nom_service, d.name as nom_medecin 
          FROM patients p 
          LEFT JOIN consultations c ON p.id = c.patient_id 
          LEFT JOIN services s ON c.service_id = s.id 
          LEFT JOIN doctors d ON c.medecin_id = d.id 
          WHERE 1=1";

$params = [];

if (!empty($_GET['q'])) {
    $query .= " AND (p.nom LIKE ? OR p.prenom LIKE ? OR p.code_patient_unique LIKE ?)";
    $params[] = "%{$_GET['q']}%"; $params[] = "%{$_GET['q']}%"; $params[] = "%{$_GET['q']}%";
}
if (!empty($_GET['service_id'])) { $query .= " AND c.service_id = ?"; $params[] = $_GET['service_id']; }
if (!empty($_GET['medecin_id'])) { $query .= " AND c.medecin_id = ?"; $params[] = $_GET['medecin_id']; }

$query .= " ORDER BY p.nom ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid p-4">
    <div class="card shadow-sm p-4 mb-4">
        <h4 class="mb-4"><i class="fas fa-search text-primary"></i> Recherche Avancée</h4>
        <form method="GET" class="row g-3">
            <div class="col-md-3"><input type="text" name="q" class="form-control" placeholder="Patient (Nom, Prénom, ID)..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"></div>
            <div class="col-md-3">
                <select name="service_id" class="form-select">
                    <option value="">Tous les services</option>
                    <?php foreach($db->query("SELECT * FROM services") as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($_GET['service_id']??'') == $s['id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="medecin_id" class="form-select">
                    <option value="">Tous les médecins</option>
                    <?php foreach($db->query("SELECT * FROM doctors") as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($_GET['medecin_id']??'') == $d['id'] ? 'selected' : '' ?>><?= $d['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrer</button></div>
        </form>
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-dark"><tr><th>Patient</th><th>Service</th><th>Médecin</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($results as $p): ?>
                <tr>
                    <td><strong><?= $p['nom'] ?> <?= $p['prenom'] ?></strong><br><small class="text-muted">ID: <?= $p['code_patient_unique'] ?></small></td>
                    <td><?= $p['nom_service'] ?? '<span class="text-danger">Non consulté</span>' ?></td>
                    <td><?= $p['nom_medecin'] ?? 'Aucun' ?></td>
                    <td><a href="suivi.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info">Dossier</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
