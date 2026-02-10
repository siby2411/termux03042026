

<?php
session_start();
require_once 'config/database.php';

$title = "Comptabilité Générale";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - SYSCOHADA Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-book me-2"></i><?php echo $title; ?>
                </h1>
                <div class="btn-group">
                    <a href="saisie_ecriture.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nouvelle Écriture
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Module Journal -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-file-invoice text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Journal Comptable</h5>
                            <p class="text-muted">Saisie et consultation des écritures</p>
                            <div class="btn-group mt-2">
                                <a href="journal_comptable.php" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Consulter
                                </a>
                                <a href="saisie_ecriture.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Ajouter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Module Grand Livre -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-book-open text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Grand Livre</h5>
                            <p class="text-muted">Consultation par compte</p>
                            <a href="grand_livre.php" class="btn btn-success mt-2">
                                <i class="fas fa-search me-2"></i>Explorer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Module Balance -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-balance-scale text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Balance Comptable</h5>
                            <p class="text-muted">Balance générale et auxiliaire</p>
                            <a href="balance.php" class="btn btn-warning text-white mt-2">
                                <i class="fas fa-calculator me-2"></i>Générer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Module Plan Comptable -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-sitemap text-info" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Plan Comptable</h5>
                            <p class="text-muted">Structure OHADA des comptes</p>
                            <a href="plan_comptable.php" class="btn btn-info mt-2">
                                <i class="fas fa-eye me-2"></i>Explorer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Module Exercices -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Exercices Comptables</h5>
                            <p class="text-muted">Gestion des périodes</p>
                            <a href="exercices_comptables.php" class="btn btn-danger mt-2">
                                <i class="fas fa-cog me-2"></i>Gérer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Module Clôture -->
                <div class="col-md-4 mb-4">
                    <div class="dashboard-card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-lock text-secondary" style="font-size: 3rem;"></i>
                            </div>
                            <h5>Clôture</h5>
                            <p class="text-muted">Procédure de clôture</p>
                            <a href="cloture_exercice.php" class="btn btn-secondary mt-2">
                                <i class="fas fa-lock me-2"></i>Clôturer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>





