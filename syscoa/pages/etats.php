<?php
// pages/etats.php - États Financiers
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="fas fa-file-contract me-2"></i>États Financiers
        </h2>
        <div>
            <button class="btn btn-primary" onclick="window.location.href='?module=bilans'">
                <i class="fas fa-eye me-1"></i>Voir les bilans
            </button>
            <button class="btn btn-outline-secondary" onclick="window.location.href='?module=compte_resultat'">
                <i class="fas fa-chart-line me-1"></i>Compte de résultat
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-balance-scale fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Bilan Comptable</h5>
                    <p class="card-text">Actif, Passif et Capitaux propres</p>
                    <a href="?module=bilans" class="btn btn-primary">Accéder</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Compte de Résultat</h5>
                    <p class="card-text">Produits, Charges et Résultat</p>
                    <a href="?module=compte_resultat" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exchange-alt fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Tableau des Flux</h5>
                    <p class="card-text">Flux de trésorerie</p>
                    <a href="?module=flux" class="btn btn-warning">Accéder</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-file-pdf me-2"></i>Export des états</h5>
                    <p>Générez des rapports PDF des états financiers :</p>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-print me-2"></i>Bilan au format PDF
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-print me-2"></i>Compte de résultat PDF
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                     <i class="fas fa-print me-2"></i>Pack complet PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-history me-2"></i>Historique</h5>
                    <p>Consultez les états des exercices précédents :</p>
                    <select class="form-select mb-3">
                        <option>Sélectionner un exercice</option>
                        <option>2025 (en cours)</option>
                        <option>2024</option>
                        <option>2023</option>
                        <option>2022</option>
                    </select>
                    <button class="btn btn-outline-primary w-100">
                        <i class="fas fa-search me-1"></i>Rechercher
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
