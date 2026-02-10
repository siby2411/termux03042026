<?php
include '../includes/header.php';
include '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Statistiques pour le tableau de bord
$stats = [];

// Patients du jour
$query = "SELECT COUNT(*) as total FROM patients WHERE DATE(date_creation) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['patients_ajourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Rendez-vous du jour
$query = "SELECT COUNT(*) as total FROM rendez_vous WHERE DATE(date_heure) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['rdv_ajourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Chiffre d'affaires du mois
$query = "SELECT SUM(montant_total) as total FROM factures 
          WHERE MONTH(date_facture) = MONTH(CURDATE()) 
          AND YEAR(date_facture) = YEAR(CURDATE())";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['ca_mois'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Analyses en attente
$query = "SELECT COUNT(*) as total FROM demandes_analyses WHERE statut = 'Demandé'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['analyses_attente'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total patients
$query = "SELECT COUNT(*) as total FROM patients";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total personnel
$query = "SELECT COUNT(*) as total FROM personnel";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_personnel'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-speedometer2 me-2"></i>Tableau de Bord
                </h2>
                <p class="text-muted mb-0">Aperçu général de l'activité de la clinique</p>
            </div>
            <div class="text-muted">
                <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Patients Aujourd'hui</h5>
                                <h3 class="fw-bold text-primary mb-0"><?php echo $stats['patients_ajourdhui']; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-plus text-primary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">RDV Aujourd'hui</h5>
                                <h3 class="fw-bold text-success mb-0"><?php echo $stats['rdv_ajourdhui']; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-check text-success fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">CA du Mois</h5>
                                <h3 class="fw-bold text-warning mb-0"><?php echo number_format($stats['ca_mois'], 0, ',', ' '); ?> FCFA</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-currency-dollar text-warning fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Analyses en Attente</h5>
                                <h3 class="fw-bold text-info mb-0"><?php echo $stats['analyses_attente']; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-clipboard-data text-info fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deuxième ligne de statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-secondary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Total Patients</h5>
                                <h3 class="fw-bold text-secondary mb-0"><?php echo $stats['total_patients']; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-people text-secondary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Personnel</h5>
                                <h3 class="fw-bold text-danger mb-0"><?php echo $stats['total_personnel']; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-badge text-danger fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning me-2"></i>Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <a href="patients/add.php" class="btn btn-outline-primary w-100 h-100 py-3">
                                    <i class="bi bi-person-plus fs-1 d-block mb-2"></i>
                                    Nouveau Patient
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="rendezvous/add.php" class="btn btn-outline-success w-100 h-100 py-3">
                                    <i class="bi bi-calendar-plus fs-1 d-block mb-2"></i>
                                    Nouveau RDV
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="personnel/add.php" class="btn btn-outline-warning w-100 h-100 py-3">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2"></i>
                                    Nouveau Personnel
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="finances/add.php" class="btn btn-outline-info w-100 h-100 py-3">
                                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                    Nouvelle Facture
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
