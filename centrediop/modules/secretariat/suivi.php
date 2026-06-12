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
    <title>Suivi des Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0"><?php require_once '../../includes/sidebar.php'; ?></div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Suivi des Patients en cours</h2>
            <div class="card shadow-sm border-0 p-4">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Médecin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // On ajoute c.id à la sélection pour le bouton de clôture
                        $query = "SELECT c.id, p.nom, p.prenom, s.name as nom_service, d.name as nom_medecin
                                  FROM consultations c
                                  JOIN patients p ON c.patient_id = p.id
                                  JOIN services s ON c.service_id = s.id
                                  LEFT JOIN doctors d ON c.medecin_id = d.id
                                  WHERE c.statut = 'en_cours'";
                        
                        $stmt = $pdo->query($query);
                        $results = $stmt->fetchAll();

                        if ($results):
                            foreach($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nom'].' '.$row['prenom']) ?></td>
                                    <td><?= htmlspecialchars($row['nom_service']) ?></td>
                                    <td><?= $row['nom_medecin'] ? htmlspecialchars($row['nom_medecin']) : 'Non assigné' ?></td>
                                    <td>
                                        <form method="POST" action="cloturer.php">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning">Clôturer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr><td colspan="4" class="text-center">Aucun patient en consultation actuellement.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
