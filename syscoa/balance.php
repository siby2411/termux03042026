<?php
session_start();
require_once 'config/database.php';

$title = "Balance Comptable";
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
                    <i class="fas fa-balance-scale me-2"></i><?php echo $title; ?>
                </h1>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <button class="btn btn-outline-success me-2">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </button>
                    <button class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>Graphique
                    </button>
                </div>
            </div>

            <!-- Type de balance -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="typeBalance" id="balanceGenerale" checked>
                                <label class="form-check-label fw-bold" for="balanceGenerale">
                                    Balance Générale
                                </label>
                                <small class="form-text text-muted d-block">Tous les comptes avec soldes</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="typeBalance" id="balanceAuxiliaire">
                                <label class="form-check-label fw-bold" for="balanceAuxiliaire">
                                    Balance Auxiliaire
                                </label>
                                <small class="form-text text-muted d-block">Par tiers (clients, fournisseurs)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="typeBalance" id="balanceAgee">
                                <label class="form-check-label fw-bold" for="balanceAgee">
                                    Balance Âgée
                                </label>
                                <small class="form-text text-muted d-block">Avec échéancier des créances</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="dashboard-card mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Compte Début</label>
                            <input type="text" class="form-control" value="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Compte Fin</label>
                            <input type="text" class="form-control" value="7">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Période du</label>
                            <input type="date" class="form-control" value="<?php echo date('Y-01-01'); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">au</label>
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Niveau</label>
                            <select class="form-select">
                                <option value="1">Classe</option>
                                <option value="2" selected>Compte</option>
                                <option value="3">Sous-compte</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-calculator me-2"></i>Calculer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Résultats de la balance -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Balance au 31 Décembre 2024
                        <small class="text-muted ms-2">(Niveau Compte)</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Compte</th>
                                    <th>Libellé</th>
                                    <th>Solde Début</th>
                                    <th>Mouvements Débit</th>
                                    <th>Mouvements Crédit</th>
                                    <th>Solde Fin Débit</th>
                                    <th>Solde Fin Crédit</th>
                                    <th>État</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Classe 1 -->
                                <tr class="table-info">
                                    <td colspan="8" class="fw-bold">CLASSE 1 - COMPTES DE CAPITAUX</td>
                                </tr>
                                <tr>
                                    <td><strong>101</strong></td>
                                    <td>Capital social</td>
                                    <td class="text-end">1,000,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">1,000,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td><span class="badge bg-success">Équilibré</span></td>
                                </tr>
                                <tr>
                                    <td><strong>106</strong></td>
                                    <td>Réserves</td>
                                    <td class="text-end">500,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">100,000.00</td>
                                    <td class="text-end">600,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td><span class="badge bg-success">Équilibré</span></td>
                                </tr>

                                <!-- Classe 2 -->
                                <tr class="table-info">
                                    <td colspan="8" class="fw-bold">CLASSE 2 - COMPTES D'IMMOBILISATIONS</td>
                                </tr>
                                <tr>
                                    <td><strong>211</strong></td>
                                    <td>Terrains</td>
                                    <td class="text-end">800,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-end">800,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td><span class="badge bg-success">Équilibré</span></td>
                                </tr>

                                <!-- Classe 5 -->
                                <tr class="table-info">
                                    <td colspan="8" class="fw-bold">CLASSE 5 - COMPTES FINANCIERS</td>
                                </tr>
                                <tr>
                                    <td><strong>512</strong></td>
                                    <td>Banque</td>
                                    <td class="text-end">250,000.00</td>
                                    <td class="text-end">2,500,000.00</td>
                                    <td class="text-end">1,500,000.00</td>
                                    <td class="text-end">1,250,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td><span class="badge bg-warning">A surveiller</span></td>
                                </tr>
                                <tr>
                                    <td><strong>53</strong></td>
                                    <td>Caisse</td>
                                    <td class="text-end">50,000.00</td>
                                    <td class="text-end">500,000.00</td>
                                    <td class="text-end">450,000.00</td>
                                    <td class="text-end">100,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td><span class="badge bg-success">Équilibré</span></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-end">TOTAUX GÉNÉRAUX:</td>
                                    <td class="text-end">2,600,000.00</td>
                                    <td class="text-end">3,000,000.00</td>
                                    <td class="text-end">2,050,000.00</td>
                                    <td class="text-end">3,750,000.00</td>
                                    <td class="text-end">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="5" class="text-end"><strong>VÉRIFICATION ÉQUILIBRE:</strong></td>
                                    <td class="text-end"><strong>3,750,000.00</strong></td>
                                    <td class="text-end"><strong>3,750,000.00</strong></td>
                                    <td><span class="badge bg-success">ÉQUILIBRE OK</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Indicateurs -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>25</h4>
                                    <p class="mb-0">Comptes Actifs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>22</h4>
                                    <p class="mb-0">Comptes Équilibrés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>3</h4>
                                    <p class="mb-0">Comptes à Surveiller</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>0</h4>
                                    <p class="mb-0">Erreurs</p>
                                </div>
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
