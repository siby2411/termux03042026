<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$db = getPDO();

// Fonction de décrémentation et historique
function delivrerMedicament($db, $medicament_nom, $patient_id) {
    // 1. Décrémenter le stock
    $stmt1 = $db->prepare("UPDATE stock SET quantite = quantite - 1 WHERE nom_produit = ? AND quantite > 0");
    $result = $stmt1->execute([$medicament_nom]);
    
    // 2. Si succès, enregistrer dans l'historique
    if ($result && $stmt1->rowCount() > 0) {
        $stmt2 = $db->prepare("INSERT INTO historique_pharmacie (medicament, patient_id) VALUES (?, ?)");
        $stmt2->execute([$medicament_nom, $patient_id]);
    }
    return $result && $stmt1->rowCount() > 0;
}

// Traitement de la délivrance
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delivrer') {
    if (delivrerMedicament($db, $_POST['medicament'], $_POST['patient_id'])) {
        $message = '<div class="alert alert-success">Médicament délivré et enregistré dans l\'historique.</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur : Stock insuffisant ou médicament introuvable.</div>';
    }
}

// Récupération des données
$prescriptions = $db->query("SELECT t.*, p.nom, p.prenom FROM traitements t JOIN patients p ON t.patient_id = p.id ORDER BY t.date_prescription DESC")->fetchAll();
$stocks = $db->query("SELECT * FROM stock ORDER BY nom_produit ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid p-4">
    <?= $message ?>
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm p-4">
                <h4><i class="fas fa-file-prescription text-primary"></i> Prescriptions en attente</h4>
                <table class="table table-hover">
                    <thead class="table-dark"><tr><th>Patient</th><th>Médicament</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($prescriptions as $tr): ?>
                        <tr>
                            <td><?= htmlspecialchars($tr['nom'] . ' ' . $tr['prenom']) ?></td>
                            <td><?= htmlspecialchars($tr['medicament']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="delivrer">
                                    <input type="hidden" name="medicament" value="<?= htmlspecialchars($tr['medicament']) ?>">
                                    <input type="hidden" name="patient_id" value="<?= htmlspecialchars($tr['patient_id']) ?>">
                                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Délivrer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <h4><i class="fas fa-warehouse text-warning"></i> Stock Actuel</h4>
                <table class="table table-sm">
                    <thead><tr><th>Produit</th><th>Qté</th><th>État</th></tr></thead>
                    <tbody>
                        <?php foreach($stocks as $s): ?>
                        <tr class="<?= ($s['quantite'] <= $s['seuil_alerte']) ? 'table-danger' : '' ?>">
                            <td><?= htmlspecialchars($s['nom_produit']) ?></td>
                            <td><?= $s['quantite'] ?></td>
                            <td><?= ($s['quantite'] <= $s['seuil_alerte']) ? '⚠️' : 'OK' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
