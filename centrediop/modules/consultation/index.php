<?php 
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';

// Requête optimisée : on récupère les consultations et on vérifie l'existence d'une facture liée
$sql = "SELECT c.id, c.date_consultation, p.nom, p.prenom, c.medecin_id, c.statut, f.id as facture_id
        FROM consultations c 
        JOIN patients p ON c.patient_id = p.id 
        LEFT JOIN factures f ON c.id = f.consultation_id
        ORDER BY c.date_consultation DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Consultations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="d-flex">
    <div style="width: 250px;"><?php include '../../includes/sidebar.php'; ?></div>
    <div class="flex-grow-1 p-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h4><i class="fas fa-list"></i> Liste des consultations</h4></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Médecin ID</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date_consultation']; ?></td>
                            <td><?php echo $row['nom'] . ' ' . $row['prenom']; ?></td>
                            <td><?php echo $row['medecin_id']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row['statut'] == 'terminee') ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($row['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="form_consultation.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <?php if($row['statut'] == 'terminee' && empty($row['facture_id'])): ?>
                                    <a href="../facturation/facturer.php?consultation_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-invoice"></i> Facturer
                                    </a>
                                <?php elseif(!empty($row['facture_id'])): ?>
                                    <span class="text-muted small"><i class="fas fa-check"></i> Facturée</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
