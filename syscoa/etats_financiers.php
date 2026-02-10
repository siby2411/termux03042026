

<?php
session_start();
require_once 'config/database.php';

$title = "États Financiers";
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
                    <i class="fas fa-file-contract me-2"></i><?php echo $title; ?>
                </h1>
                <div class="btn-group">
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </button>
                </div>
            </div>

            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Génération des États Financiers</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <i class="fas fa-balance-scale-left text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Bilan Comptable</h5>
                                    <p class="text-muted">Actif = Passif + Capitaux propres</p>
                                    <a href="bilan-comptable.php" class="btn btn-primary mt-2">
                                        <i class="fas fa-eye me-2"></i>Générer
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <i class="fas fa-chart-line text-success" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Compte de Résultat</h5>
                                    <p class="text-muted">Produits - Charges = Résultat</p>
                                    <a href="compte_resultat.php" class="btn btn-success mt-2">
                                        <i class="fas fa-eye me-2"></i>Générer
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 text-center">
                                <div class="card-body">
                                    <i class="fas fa-exchange-alt text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">Flux de Trésorerie</h5>
                                    <p class="text-muted">Exploitation, Investissement, Financement</p>
                                    <a href="tableau_flux_tresorerie.php" class="btn btn-warning text-white mt-2">
                                        <i class="fas fa-eye me-2"></i>Générer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rapports prédéfinis -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Modèles de Rapports</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Rapport Mensuel</h6>
                                        <small>3 min</small>
                                    </div>
                                    <p class="mb-1">Bilan condensé + Compte de résultat mensuel</p>
                                    <small>Inclut comparaison avec mois précédent</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Rapport Trimestriel</h6>
                                        <small>5 min</small>
                                    </div>
                                    <p class="mb-1">États financiers complets + Analyse des ratios</p>
                                    <small>Conforme aux exigences réglementaires</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-group">
                                <a href="rapports_ohada.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Rapport OHADA Complet</h6>
                                        <small>7 min</small>
                                    </div>
                                    <p class="mb-1">Liasse fiscale complète SYSCOHADA</p>
                                    <small>Bilan, CR, Annexe, Tableaux réglementaires</small>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Rapport de Gestion</h6>
                                        <small>4 min</small>
                                    </div>
                                    <p class="mb-1">Tableaux de bord directionnels</p>
                                    <small>KPIs, Graphiques, Analyse de performance</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>

