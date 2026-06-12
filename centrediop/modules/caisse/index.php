<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();
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
            <h2 class="mb-4"><i class="fas fa-cash-register"></i> Espace Caisse</h2>
            <div class="card shadow-sm border-0 p-4">
                <h4 class="mb-3">File d'attente des paiements</h4>
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Montant</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT c.id, p.nom, p.prenom, s.nom_service, c.prix
                                            FROM consultations c
                                            JOIN patients p ON c.patient_id = p.id
                                            JOIN services s ON c.service_id = s.id
                                            WHERE c.statut = 'attente_paiement'");
                        $results = $stmt->fetchAll();
                        if ($results) {
                            foreach($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></td>
                                    <td><?= htmlspecialchars($row['nom_service']) ?></td>
                                    <td><?= number_format($row['prix'], 0, ',', ' ') ?> FCFA</td>
                                    <td><a href="encaisser.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Encaisser</a></td>
                                </tr>
                            <?php endforeach;
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>Aucun paiement en attente.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
