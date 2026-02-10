<?php
// modules/rapports.php
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-chart-bar"></i> Rapports Financiers</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Liste des rapports disponibles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                            <h5>Balance générale</h5>
                            <a href="balance.php" class="btn btn-primary">Générer</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                            <h5>Grand livre</h5>
                            <a href="grand_livre.php" class="btn btn-success">Générer</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-pie fa-3x text-warning mb-3"></i>
                            <h5>Compte de résultat</h5>
                            <a href="compte_resultat.php" class="btn btn-warning">Générer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
