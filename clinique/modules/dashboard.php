<?php
include '../includes/header.php';
include '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Statistiques dynamiques
$stats = ['patients' => 0, 'consultations' => 0, 'rdv' => 0];
try {
    $stats['patients'] = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn() ?: 0;
    $stats['consultations'] = $db->query("SELECT COUNT(*) FROM consultations WHERE DATE(date_creation) = CURDATE()")->fetchColumn() ?: 0;
    $stats['rdv'] = $db->query("SELECT COUNT(*) FROM rendezvous WHERE DATE(date_rdv) = CURDATE()")->fetchColumn() ?: 0;
} catch (Exception $e) { /* Protection si colonnes manquantes */ }
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold text-primary"><i class="bi bi-hospital me-2"></i>Clinique Oméga</h2>
        <span class="badge bg-primary px-3 py-2"><?php echo date('d/m/Y'); ?></span>
    </div>

    <div class="row g-3 mb-4 text-center">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4 p-3">
                <div class="small text-muted text-uppercase">Total Patients</div>
                <h2 class="fw-bold text-primary mb-0"><?= $stats['patients'] ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-danger border-4 p-3">
                <div class="small text-muted text-uppercase">Consultations Jour</div>
                <h2 class="fw-bold text-danger mb-0"><?= $stats['consultations'] ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-success border-4 p-3">
                <div class="small text-muted text-uppercase">Rendez-vous Jour</div>
                <h2 class="fw-bold text-success mb-0"><?= $stats['rdv'] ?></h2>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">Gestion des Modules</div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-md-3">
                    <a href="patients/list.php" class="btn btn-outline-primary w-100 py-4 shadow-sm">
                        <i class="bi bi-people-fill fs-1 d-block mb-2"></i> Patients
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="consultations/list.php" class="btn btn-outline-danger w-100 py-4 shadow-sm">
                        <i class="bi bi-stethoscope fs-1 d-block mb-2"></i> Consultations
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="rendezvous/list.php" class="btn btn-outline-success w-100 py-4 shadow-sm">
                        <i class="bi bi-calendar-check-fill fs-1 d-block mb-2"></i> Rendez-vous
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="personnel/list.php" class="btn btn-outline-secondary w-100 py-4 shadow-sm">
                        <i class="bi bi-person-badge-fill fs-1 d-block mb-2"></i> Personnel
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="analyses/list.php" class="btn btn-outline-info w-100 py-4 shadow-sm">
                        <i class="bi bi-droplet-fill fs-1 d-block mb-2"></i> Analyses
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="finances/list.php" class="btn btn-outline-warning w-100 py-4 shadow-sm">
                        <i class="bi bi-cash-coin fs-1 d-block mb-2"></i> Finances
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
