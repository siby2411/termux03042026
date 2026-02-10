<?php
// Page Tableau de bord
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord SYSCOHADA</h5>
            </div>
            <div class="card-body">
                <h4>Bienvenue dans SYSCOHADA v2.0 !</h4>
                <p class="lead">Votre système comptable conforme aux normes OHADA est maintenant opérationnel.</p>
                
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-book fa-3x text-success mb-3"></i>
                                <h5>Journal</h5>
                                <p>Saisie des écritures comptables</p>
                                <a href="?module=journal" class="btn btn-success">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-file-invoice fa-3x text-info mb-3"></i>
                                <h5>Grand Livre</h5>
                                <p>Consultation analytique</p>
                                <a href="?module=grand_livre" class="btn btn-info">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                                <h5>SIG</h5>
                                <p>Soldes Intermédiaires de Gestion</p>
                                <a href="?module=sig" class="btn btn-warning">Accéder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-percentage fa-3x text-danger mb-3"></i>
                                <h5>Ratios</h5>
                                <p>Analyse financière</p>
                                <a href="?module=ratios" class="btn btn-danger">Accéder</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-4">
                    <h5><i class="fas fa-info-circle me-2"></i>Fonctionnalités disponibles :</h5>
                    <ul>
                        <li><strong>Journal Comptable</strong> - Saisie et consultation des écritures</li>
                        <li><strong>Grand Livre</strong> - Consultation détaillée par compte</li>
                        <li><strong>Balance</strong> - Balance générale et âgée</li>
                        <li><strong>SIG</strong> - Soldes Intermédiaires de Gestion</li>
                        <li><strong>Ratios</strong> - Analyse financière complète</li>
                        <li><strong>États Financiers</strong> - Bilan, Compte de résultat, Tableau de flux</li>
                        <li><strong>Budget</strong> - Gestion budgétaire</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
