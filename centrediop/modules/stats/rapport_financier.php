<?php
session_start();
// Remontée de deux niveaux depuis /modules/stats/ pour accéder à la racine
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$pdo = getPDO();

$date_debut = $_GET['date_debut'] ?? date('Y-m-d');
$date_fin = $_GET['date_fin'] ?? date('Y-m-d');
$service_id = $_GET['service_id'] ?? '';

// Construction de la requête
$sql = "SELECT p.date_paiement, p.montant_paye, s.name as service_nom, d.name as medecin_nom, pat.nom as patient_nom
        FROM paiements p
        JOIN consultations c ON p.consultation_id = c.id
        JOIN services s ON c.service_id = s.id
        JOIN doctors d ON c.medecin_id = d.id
        JOIN patients pat ON c.patient_id = pat.id
        WHERE DATE(p.date_paiement) BETWEEN ? AND ?";

$params = [$date_debut, $date_fin];

if (!empty($service_id)) {
    $sql .= " AND c.service_id = ?";
    $params[] = $service_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$paiements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Financier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card p-4 shadow-sm border-0">
        <h3 class="mb-4"><i class="fas fa-file-invoice-dollar"></i> États Financiers</h3>
        <a href="/modules/admin/dashboard.php" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
        
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Date Début</label>
                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date Fin</label>
                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Service</label>
                <select name="service_id" class="form-select">
                    <option value="">Tous les services</option>
                    <?php 
                    $services = $pdo->query("SELECT id, name FROM services")->fetchAll();
                    foreach($services as $s) {
                        $selected = ($service_id == $s['id']) ? 'selected' : '';
                        echo "<option value='{$s['id']}' $selected>{$s['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>

        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>Date</th><th>Patient</th><th>Service</th><th>Médecin</th><th>Montant</th></tr>
            </thead>
            <tbody>
                <?php 
                $total = 0; 
                if ($paiements):
                    foreach($paiements as $p): $total += $p['montant_paye']; ?>
                    <tr>
                        <td><?= htmlspecialchars($p['date_paiement']) ?></td>
                        <td><?= htmlspecialchars($p['patient_nom']) ?></td>
                        <td><?= htmlspecialchars($p['service_nom']) ?></td>
                        <td><?= htmlspecialchars($p['medecin_nom']) ?></td>
                        <td><?= number_format($p['montant_paye'], 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <?php endforeach; 
                else: ?>
                    <tr><td colspan="5" class="text-center">Aucune donnée trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-active">
                <tr>
                    <td colspan="4" class="text-end"><strong>TOTAL RECETTES</strong></td>
                    <td><strong><?= number_format($total, 0, ',', ' ') ?> FCFA</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
