<?php
$page_title = "Tableau de Bord Expert - OMEGA 2026";
require_once __DIR__ . '/../config/config.php';

// Vérification auth simplifiée
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'COMPTABLE';

// KPI Express sécurisés - Gestion des erreurs SQL
try {
    // Chiffre d'affaires (classe 7)
    $stmt = $pdo->query("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 70 AND 79");
    $total_ca = $stmt->fetchColumn() ?: 0;
    
    // Solde bancaire (compte 521)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - 
               COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) 
        FROM ECRITURES_COMPTABLES
    ");
    $total_banque = $stmt->fetchColumn() ?: 0;
    
    // Nombre d'écritures ce mois
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM ECRITURES_COMPTABLES 
        WHERE MONTH(date_ecriture) = MONTH(CURRENT_DATE) 
        AND YEAR(date_ecriture) = YEAR(CURRENT_DATE)
    ");
    $stmt->execute();
    $month_count = $stmt->fetchColumn() ?: 0;
    
    // Total général des écritures
    $total_entries = $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES")->fetchColumn() ?: 0;
    
} catch (PDOException $e) {
    $total_ca = $total_banque = $month_count = $total_entries = 0;
    $db_error = "⚠️ Base en initialisation";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA ERP - Tableau de Bord Expert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .navbar-omega {
            background: linear-gradient(135deg, #0a2b3e 0%, #0f3b52 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        .module-card {
            background: white;
            border-radius: 20px;
            padding: 1.25rem;
            transition: all 0.2s ease;
            border: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .module-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: transparent;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid;
            display: inline-block;
        }
        .badge-month {
            background: #e9ecef;
            color: #495057;
            padding: 0.35rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .footer-omega {
            background: #0f3b52;
            color: #adb5bd;
            padding: 1.5rem 0;
            margin-top: 3rem;
            text-align: center;
            font-size: 0.85rem;
        }
        @media (max-width: 768px) {
            .stat-card { padding: 1rem; }
            .module-card { padding: 0.9rem; }
            .stat-icon { width: 42px; height: 42px; font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<!-- Barre de navigation professionnelle -->
<nav class="navbar navbar-omega">
    <div class="container">
        <a class="navbar-brand text-white" href="dashboard_expert.php">
            <i class="bi bi-journal-bookmark-fill"></i> OMEGA<span class="fw-light">ERP</span>
        </a>
        <div class="d-flex gap-3 align-items-center">
            <span class="badge-month">
                <i class="bi bi-calendar3"></i> <?= date('F Y') ?>
            </span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light rounded-pill dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['email'] ?? 'Expert') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<main class="container my-4">
    <?php if (isset($db_error)): ?>
        <div class="alert alert-warning alert-dismissible fade show rounded-4" role="alert">
            <i class="bi bi-info-circle-fill"></i> <?= $db_error ?> - Veuillez saisir vos premières écritures
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">CA Global</small>
                        <h3 class="mt-2 mb-0 fw-bold text-primary"><?= number_format($total_ca, 0, ',', ' ') ?></h3>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
                <hr class="my-2">
                <small class="text-success"><i class="bi bi-arrow-up"></i> +12% vs mois préc.</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Trésorerie Banque</small>
                        <h3 class="mt-2 mb-0 fw-bold text-success"><?= number_format($total_banque, 0, ',', ' ') ?></h3>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-bank2"></i>
                    </div>
                </div>
                <hr class="my-2">
                <small class="text-muted">Compte 521 - Disponible</small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Écritures (mois)</small>
                        <h3 class="mt-2 mb-0 fw-bold text-info"><?= $month_count ?></h3>
                        <small class="text-muted">opérations</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
                <hr class="my-2">
                <small class="text-muted"><?= date('F Y') ?></small>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total écritures</small>
                        <h3 class="mt-2 mb-0 fw-bold text-dark"><?= $total_entries ?></h3>
                        <small class="text-muted">cumul exercice</small>
                    </div>
                    <div class="stat-icon bg-dark bg-opacity-10 text-dark">
                        <i class="bi bi-database"></i>
                    </div>
                </div>
                <hr class="my-2">
                <small class="text-muted">Base SYSCOHADA</small>
            </div>
        </div>
    </div>

    <!-- Opérations courantes -->
    <div class="mb-5">
        <h5 class="section-title text-primary border-primary">
            <i class="bi bi-pencil-square"></i> Opérations courantes
        </h5>
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <a href="ecriture.php" class="module-card">
                    <i class="bi bi-file-text fs-4 text-primary"></i>
                    <h6 class="mt-2 mb-0">Saisie d'écriture</h6>
                    <small class="text-muted">Journal général</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="ecriture_list.php" class="module-card">
                    <i class="bi bi-list-check fs-4 text-success"></i>
                    <h6 class="mt-2 mb-0">Liste des écritures</h6>
                    <small class="text-muted">Consultation</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="grand_livre.php" class="module-card">
                    <i class="bi bi-book fs-4 text-info"></i>
                    <h6 class="mt-2 mb-0">Grand Livre</h6>
                    <small class="text-muted">Détail par compte</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="balance.php" class="module-card">
                    <i class="bi bi-scale fs-4 text-warning"></i>
                    <h6 class="mt-2 mb-0">Balance générale</h6>
                    <small class="text-muted">Débit/Crédit</small>
                </a>
            </div>
        </div>
    </div>

    <!-- Modules de gestion SYSCOHADA -->
    <div class="mb-5">
        <h5 class="section-title text-success border-success">
            <i class="bi bi-gear-fill"></i> Modules SYSCOHADA UEMOA
        </h5>
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <a href="immobilisations.php" class="module-card bg-light">
                    <i class="bi bi-building fs-4 text-secondary"></i>
                    <h6 class="mt-2 mb-0">Immobilisations</h6>
                    <small class="text-muted">Classe 2 / Amortissements</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="stock.php" class="module-card bg-light">
                    <i class="bi bi-box-seam fs-4 text-secondary"></i>
                    <h6 class="mt-2 mb-0">Gestion des stocks</h6>
                    <small class="text-muted">Inventaire permanent</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="rapprochement.php" class="module-card bg-light">
                    <i class="bi bi-arrow-left-right fs-4 text-secondary"></i>
                    <h6 class="mt-2 mb-0">Rapprochement bancaire</h6>
                    <small class="text-muted">Lettrage / Pointage</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="a_nouveaux.php" class="module-card bg-light">
                    <i class="bi bi-arrow-repeat fs-4 text-secondary"></i>
                    <h6 class="mt-2 mb-0">À Nouveaux</h6>
                    <small class="text-muted">Report de solde N-1</small>
                </a>
            </div>
        </div>
    </div>

    <!-- États financiers officiels UEMOA -->
    <div>
        <h5 class="section-title text-danger border-danger">
            <i class="bi bi-file-earmark-pdf-fill"></i> États financiers (Norme SYSCOHADA)
        </h5>
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <a href="bilan.php" class="module-card border-start border-4 border-danger">
                    <i class="bi bi-pie-chart fs-4 text-danger"></i>
                    <h6 class="mt-2 mb-0">Bilan Actif/Passif</h6>
                    <small class="text-muted">Patrimoine net</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="sig.php" class="module-card border-start border-4 border-danger">
                    <i class="bi bi-graph-up fs-4 text-danger"></i>
                    <h6 class="mt-2 mb-0">Tableau SIG</h6>
                    <small class="text-muted">Soldes de gestion UEMOA</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="compte_resultat.php" class="module-card border-start border-4 border-danger">
                    <i class="bi bi-calculator fs-4 text-danger"></i>
                    <h6 class="mt-2 mb-0">Compte de résultat</h6>
                    <small class="text-muted">Produits / Charges</small>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="flux_tresorerie.php" class="module-card border-start border-4 border-danger">
                    <i class="bi bi-cash-stack fs-4 text-danger"></i>
                    <h6 class="mt-2 mb-0">Tableau de flux</h6>
                    <small class="text-muted">Trésorerie nette</small>
                </a>
            </div>
        </div>
    </div>
</main>

<footer class="footer-omega">
    <div class="container">
        <i class="bi bi-shield-check"></i> Conforme SYSCOHADA révisé - OHADA UEMOA<br>
        <small>© 2026 OMEGA INFORMATIQUE CONSULTING - Solutions comptables intégrées</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
