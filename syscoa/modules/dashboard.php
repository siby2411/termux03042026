<?php
// modules/dashboard.php
$id_exercice = $_SESSION['id_exercice'];

// Statistiques
$total_ecritures = $pdo->query("SELECT COUNT(*) FROM ecritures WHERE id_exercice = $id_exercice")->fetchColumn();
$solde_banque = $pdo->query("SELECT SUM(debit - credit) FROM ecritures WHERE compte_num LIKE '52%'")->fetchColumn();
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles_stock")->fetchColumn();
$total_tiers = $pdo->query("SELECT COUNT(*) FROM tiers")->fetchColumn();
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Écritures</h6>
                    <h2><?php echo number_format($total_ecritures, 0, ',', ' '); ?></h2>
                    <i class="fas fa-file-invoice fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Solde bancaire</h6>
                    <h2><?php echo format_montant($solde_banque); ?></h2>
                    <i class="fas fa-university fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Articles stock</h6>
                    <h2><?php echo number_format($total_articles, 0, ',', ' '); ?></h2>
                    <i class="fas fa-boxes fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Tiers actifs</h6>
                    <h2><?php echo number_format($total_tiers, 0, ',', ' '); ?></h2>
                    <i class="fas fa-users fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Accès rapide -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Accès rapide</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="?module=ecritures" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle"></i> Écriture
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=rapprochement" class="btn btn-success w-100">
                                <i class="fas fa-sync-alt"></i> Rapprochement
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=articles" class="btn btn-warning w-100">
                                <i class="fas fa-box"></i> Stock
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=cloture" class="btn btn-danger w-100">
                                <i class="fas fa-lock"></i> Clôture
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Analyses</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="?module=soldes" class="btn btn-info w-100">
                                <i class="fas fa-chart-pie"></i> Soldes SIG
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=bilans" class="btn btn-info w-100">
                                <i class="fas fa-balance-scale"></i> Bilan
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=flux" class="btn btn-info w-100">
                                <i class="fas fa-exchange-alt"></i> Flux trésorerie
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=rapports" class="btn btn-info w-100">
                                <i class="fas fa-file-alt"></i> Rapports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dernières activités -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Activités récentes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Système SYSCO OHADA v2.0 - Architecture modulaire</p>
                    <p>Bienvenue dans la nouvelle interface unifiée !</p>
                </div>
            </div>
        </div>
    </div>
</div>
