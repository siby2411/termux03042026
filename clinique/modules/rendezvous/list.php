<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Rendez-vous du jour par défaut
$date = $_GET['date'] ?? date('Y-m-d');

$query = "SELECT rv.*, 
                 p.code_patient, p.nom as patient_nom, p.prenom as patient_prenom, 
                 pers.matricule, pers.nom as medecin_nom, pers.prenom as medecin_prenom,
                 d.nom as departement_nom
          FROM rendez_vous rv
          JOIN patients p ON rv.id_patient = p.id
          JOIN personnel pers ON rv.id_medecin = pers.id
          JOIN departements d ON rv.id_departement = d.id
          WHERE DATE(rv.date_heure) = :date
          ORDER BY rv.date_heure";
$stmt = $db->prepare($query);
$stmt->bindParam(':date', $date);
$stmt->execute();
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques pour la journée
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'Planifié' THEN 1 ELSE 0 END) as planifies,
    SUM(CASE WHEN statut = 'Confirmé' THEN 1 ELSE 0 END) as confirmes,
    SUM(CASE WHEN statut = 'En Cours' THEN 1 ELSE 0 END) as en_cours,
    SUM(CASE WHEN statut = 'Terminé' THEN 1 ELSE 0 END) as termines
    FROM rendez_vous 
    WHERE DATE(date_heure) = :date";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->bindParam(':date', $date);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-calendar-event me-2"></i>Gestion des Rendez-vous
                </h2>
                <p class="text-muted mb-0">Planning des consultations médicales</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nouveau RDV
            </a>
        </div>

        <!-- Sélecteur de date et statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <label for="dateSelector" class="form-label fw-semibold">Date des rendez-vous</label>
                        <input type="date" id="dateSelector" value="<?php echo $date; ?>" class="form-control">
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="card border-start border-primary border-4">
                            <div class="card-body text-center p-3">
                                <h4 class="fw-bold text-primary mb-1"><?php echo $stats['total']; ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-start border-warning border-4">
                            <div class="card-body text-center p-3">
                                <h4 class="fw-bold text-warning mb-1"><?php echo $stats['planifies']; ?></h4>
                                <small class="text-muted">Planifiés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-start border-info border-4">
                            <div class="card-body text-center p-3">
                                <h4 class="fw-bold text-info mb-1"><?php echo $stats['confirmes']; ?></h4>
                                <small class="text-muted">Confirmés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-start border-success border-4">
                            <div class="card-body text-center p-3">
                                <h4 class="fw-bold text-success mb-1"><?php echo $stats['termines']; ?></h4>
                                <small class="text-muted">Terminés</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des rendez-vous -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Rendez-vous du <?php echo date('d/m/Y', strtotime($date)); ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Heure</th>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Département</th>
                                <th>Motif</th>
                                <th>Statut</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rendezvous) > 0): ?>
                                <?php foreach ($rendezvous as $rdv): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong class="text-primary">
                                            <?php echo date('H:i', strtotime($rdv['date_heure'])); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-success"><?php echo htmlspecialchars($rdv['code_patient']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($rdv['patient_prenom'] . ' ' . $rdv['patient_nom']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-info"><?php echo htmlspecialchars($rdv['matricule']); ?></strong><br>
                                            <small class="text-muted">Dr. <?php echo htmlspecialchars($rdv['medecin_prenom'] . ' ' . $rdv['medecin_nom']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($rdv['departement_nom']); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($rdv['motif']); ?>">
                                            <?php echo htmlspecialchars(substr($rdv['motif'] ?? '', 0, 50)); ?>
                                            <?php if (strlen($rdv['motif'] ?? '') > 50): ?>...<?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status statut-<?php echo strtolower($rdv['statut']); ?>">
                                            <?php echo htmlspecialchars($rdv['statut']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <?php if ($rdv['statut'] == 'Planifié'): ?>
                                                <a href="action.php?action=confirm&id=<?php echo $rdv['id']; ?>" class="btn btn-sm btn-outline-success btn-action" title="Confirmer">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (in_array($rdv['statut'], ['Planifié', 'Confirmé'])): ?>
                                                <a href="action.php?action=start&id=<?php echo $rdv['id']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Démarrer">
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($rdv['statut'] == 'En Cours'): ?>
                                                <a href="../consultations/add.php?rdv_id=<?php echo $rdv['id']; ?>" class="btn btn-sm btn-outline-info btn-action" title="Consulter">
                                                    <i class="bi bi-heart-pulse"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="edit.php?id=<?php echo $rdv['id']; ?>" class="btn btn-sm btn-outline-warning btn-action" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                                            Aucun rendez-vous prévu pour le <?php echo date('d/m/Y', strtotime($date)); ?>
                                        </div>
                                        <a href="add.php" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-circle me-2"></i>Planifier un rendez-vous
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (count($rendezvous) > 0): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <?php echo count($rendezvous); ?> rendez-vous pour cette date
                    </small>
                    <small class="text-muted">
                        Dernière mise à jour : <?php echo date('H:i:s'); ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badge-status {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
    font-weight: 500;
}
.statut-planifié { background-color: #f59e0b; color: white; }
.statut-confirmé { background-color: #3b82f6; color: white; }
.statut-en cours { background-color: #8b5cf6; color: white; }
.statut-terminé { background-color: #10b981; color: white; }
.statut-annulé { background-color: #ef4444; color: white; }

.btn-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-action:hover {
    transform: translateY(-1px);
}
</style>

<script>
document.getElementById('dateSelector').addEventListener('change', function() {
    window.location.href = 'list.php?date=' + this.value;
});

// Auto-refresh toutes les 30 secondes
setTimeout(() => {
    window.location.reload();
}, 30000);
</script>

<?php include '../../includes/footer.php'; ?>
