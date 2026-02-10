<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT da.*, ta.nom as type_analyse, ta.prix, 
                 p.code_patient, p.nom as patient_nom, p.prenom as patient_prenom,
                 pers.matricule, pers.nom as technicien_nom, pers.prenom as technicien_prenom,
                 c.date_consultation
          FROM demandes_analyses da
          JOIN type_analyses ta ON da.id_type_analyse = ta.id
          JOIN consultations c ON da.id_consultation = c.id
          JOIN patients p ON c.id_patient = p.id
          LEFT JOIN personnel pers ON da.id_technicien = pers.id
          ORDER BY da.date_demande DESC
          LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$analyses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-clipboard-data me-2"></i>Demandes d'Analyses
                </h2>
                <p class="text-muted mb-0">Gestion des analyses biologiques</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Liste des Demandes d'Analyses</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date Demande</th>
                                <th>Patient</th>
                                <th>Type d'Analyse</th>
                                <th>Prix</th>
                                <th>Technicien</th>
                                <th>Statut</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($analyses) > 0): ?>
                                <?php foreach ($analyses as $analyse): ?>
                                <tr>
                                    <td class="ps-4">
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($analyse['date_demande'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-success"><?php echo htmlspecialchars($analyse['code_patient']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($analyse['patient_prenom'] . ' ' . $analyse['patient_nom']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($analyse['type_analyse']); ?></td>
                                    <td><?php echo number_format($analyse['prix'], 0, ',', ' '); ?> FCFA</td>
                                    <td>
                                        <?php if ($analyse['matricule']): ?>
                                            <div>
                                                <strong class="text-info"><?php echo htmlspecialchars($analyse['matricule']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($analyse['technicien_prenom'] . ' ' . $analyse['technicien_nom']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-status statut-<?php echo strtolower($analyse['statut']); ?>">
                                            <?php echo htmlspecialchars($analyse['statut']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?php echo $analyse['id']; ?>" class="btn btn-sm btn-outline-primary btn-action">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-clipboard-x display-4 d-block mb-3"></i>
                                            Aucune demande d'analyse trouvée
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-status {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}
.statut-demandé { background-color: #f59e0b; color: white; }
.statut-en cours { background-color: #3b82f6; color: white; }
.statut-terminé { background-color: #10b981; color: white; }
.statut-rendu { background-color: #8b5cf6; color: white; }
</style>

<?php include '../../includes/footer.php'; ?>
