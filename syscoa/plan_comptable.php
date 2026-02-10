<?php
session_start();
require_once 'config/database.php';

$title = "Plan Comptable OHADA";
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
                    <i class="fas fa-sitemap me-2"></i><?php echo $title; ?>
                </h1>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <button class="btn btn-outline-success me-2">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjoutCompte">
                        <i class="fas fa-plus me-2"></i>Nouveau Compte
                    </button>
                </div>
            </div>

            <!-- Recherche et filtres -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-mdd-4">
                            <input type="text" class="form-control" placeholder="Rechercher un compte..." id="searchCompte">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterClasse">
                                <option value="">Toutes les classes</option>
                                <option value="1">Classe 1 - Capitaux</option>
                                <option value="2">Classe 2 - Immobilisations</option>
                                <option value="3">Classe 3 - Stocks</option>
                                <option value="4">Classe 4 - Tiers</option>
                                <option value="5">Classe 5 - Financiers</option>
                                <option value="6">Classe 6 - Charges</option>
                                <option value="7">Classe 7 - Produits</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="filterType">
                                <option value="">Tous types</option>
                                <option value="generaux">Comptes généraux</option>
                                <option value="auxiliaires">Comptes auxiliaires</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100">
                                <button class="btn btn-primary" id="btnSearch">
                                    <i class="fas fa-search me-2"></i>Rechercher
                                </button>
                                <button class="btn btn-outline-secondary" id="btnReset">
                                    <i class="fas fa-redo me-2"></i>Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan comptable -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Structure du Plan Comptable OHADA</h5>
                </div>
                <div class="card-body">
                    <!-- Classe 1 -->
                    <div class="accordion" id="accordionPlan">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    <strong>CLASSE 1 - COMPTES DE CAPITAUX</strong>
                                    <span class="badge bg-primary ms-2">12 comptes</span>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Compte</th>
                                                <th>Libellé</th>
                                                <th>Type</th>
                                                <th>Nature</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>101</strong></td>
                                                <td>Capital social</td>
                                                <td><span class="badge bg-info">Général</span></td>
                                                <td>Crédit</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>106</strong></td>
                                                <td>Réserves</td>
                                                <td><span class="badge bg-info">Général</span></td>
                                                <td>Crédit</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>120</strong></td>
                                                <td>Résultat de l'exercice</td>
                                                <td><span class="badge bg-info">Général</span></td>
                                                <td>Crédit/Débit</td>
                                                <td><span class="badge bg-success">Actif</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Classe 2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    <strong>CLASSE 2 - COMPTES D'IMMOBILISATIONS</strong>
                                    <span class="badge bg-primary ms-2">15 comptes</span>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <!-- Contenu de la classe 2 -->
                                </div>
                            </div>
                        </div>

                        <!-- Autres classes... -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajout Compte -->
    <div class="modal fade" id="modalAjoutCompte" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Ajouter un Nouveau Compte</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="controllers/ajout_compte.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Numéro de compte *</label>
                                <input type="text" class="form-control" name="numero" required 
                                       placeholder="Ex: 4111">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Classe *</label>
                                <select class="form-select" name="classe" required>
                                    <option value="1">1 - Capitaux</option>
                                    <option value="2">2 - Immobilisations</option>
                                    <option value="3">3 - Stocks</option>
                                    <option value="4">4 - Tiers</option>
                                    <option value="5">5 - Financiers</option>
                                    <option value="6">6 - Charges</option>
                                    <option value="7">7 - Produits</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">Libellé du compte *</label>
                                <input type="text" class="form-control" name="libelle" required 
                                       placeholder="Ex: Clients - Ventes de marchandises">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Type de compte *</label>
                                <select class="form-select" name="type" required>
                                    <option value="generique">Compte générique</option>
                                    <option value="analytique">Compte analytique</option>
                                    <option value="auxiliaire">Compte auxiliaire</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nature du solde</label>
                                <select class="form-select" name="nature">
                                    <option value="debit">Débit</option>
                                    <option value="credit">Crédit</option>
                                    <option value="mixte">Débit/Crédit</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Compte parent</label>
                                <select class="form-select" name="parent">
                                    <option value="">Sans parent</option>
                                    <option value="411">411 - Clients</option>
                                    <option value="401">401 - Fournisseurs</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer le compte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>
</body>
</html>
