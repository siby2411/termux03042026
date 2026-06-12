<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

// Requête pour lister uniquement les patients en attente de paiement
$query = "SELECT c.id, p.nom, p.prenom, c.prix, s.name as service_nom
          FROM consultations c
          JOIN patients p ON c.patient_id = p.id
          JOIN services s ON c.service_id = s.id
          WHERE c.statut = 'attente_paiement'";

$stmt = $pdo->query($query);
$attente = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Caisse - Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card shadow-sm border-0 p-4">
        <h3 class="mb-4"><i class="fas fa-cash-register"></i> File d'attente Caisse</h3>
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>Patient</th><th>Service</th><th>Montant dû</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php if ($attente): foreach($attente as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['service_nom']) ?></td>
                    <td><strong><?= number_format($row['prix'], 0, ',', ' ') ?> FCFA</strong></td>
                    <td>
                        <a href="payer.php?consultation_id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-money-bill-wave"></i> Encaisser
                        </a>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="4" class="text-center">Aucun paiement en attente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
