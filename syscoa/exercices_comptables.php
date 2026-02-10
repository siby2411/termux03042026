<?php
session_start();
require_once 'config/database.php';

$title = "Gestion des Exercices Comptables";
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
                    <i class="fas fa-calendar-alt me-2"></i><?php echo $title; ?>
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNouvelExercice">
                    <i class="fas fa-plus me-2"></i>Nouvel Exercice
                </button>
            </div>

            <!-- Exercice courant -->
            <div class="dashboard-card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-play-circle me-2"></i>Exercice Comptable Actif</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-success">2024</h5>
                                    <p class="mb-0">Exercice Fiscal</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>01/01/2024</h5>
                                    <p class="mb-0">Date début</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5>31/12/2024</h5>
                                    <p class="mb-0">Date fin</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning">
                                <div class="card-body text-center text-white">
                                    <h5>23 jours</h5>
                                    <p class="mb-0">Jours restants</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <button class="btn btn-outline-danger me-2">
                            <i class="fas fa-lock me-2"></i>Clôturer l'Exercice
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-cog me-2"></i>Paramètres
                        </button>
                    </div>
                </div>
            </div>

            <!-- Historique des exercices -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique des Exercices</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Exercice</th>
                                    <th>Période</th>
                                    <th>Statut</th>
                                    <th>Écritures</th>
                                    <th>CA Réalisé</th>
                                    <th>Date Clôture</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>2024</strong></td>
                                    <td>01/01/2024 - 31/12/2024</td>
                                    <td><span class="badge bg-success">Actif</span></td>
                                    <td>1,248</td>
                                    <td>2,500,000 FCFA</td>
                                    <td>-</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>2023</strong></td>
                                    <td>01/01/2023 - 31/12/2023</td>
                                    <td><span class="badge bg-secondary">Clôturé</span></td>
                                    <td>980</td>
                                    <td>1,850,000 FCFA</td>
                                    <td>15/02/2024</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>2022</strong></td>
                                    <td>01/01/2022 - 31/12/2022</td>
                                    <td><span class="badge bg-secondary">Clôturé</span></td>
                                    <td>745</td>
                                    <td>1,200,000 FCFA</td>
                                    <td>10/02/2023</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nouvel Exercice -->
    <div class="modal fade" id="modalNouvelExercice" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Créer un Nouvel Exercice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="controllers/creer_exercice.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Libellé de l'exercice *</label>
                            <input type="text" class="form-control" name="libelle" required 
                                   placeholder="Ex: Exercice Fiscal 2025">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Date de début *</label>
                                <input type="date" class="form-control" name="date_debut" required 
                                       value="2025-01-01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de fin *</label>
                                <input type="date" class="form-control" name="date_fin" required 
                                       value="2025-12-31">
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actif" id="actifImmediat">
                                <label class="form-check-label" for="actifImmediat">
                                    Activer immédiatement cet exercice
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reprendre_soldes" id="reprendreSoldes">
                                <label class="form-check-label" for="reprendreSoldes">
                                    Reprendre les soldes de l'exercice précédent
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer l'exercice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
