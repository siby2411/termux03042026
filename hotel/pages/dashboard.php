<?php
// Stats en temps réel
$nb_chambres = $pdo->query("SELECT COUNT(*) FROM chambres")->fetchColumn();
$nb_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$nb_staff = $pdo->query("SELECT COUNT(*) FROM personnel")->fetchColumn();
$masse_salariale = $pdo->query("SELECT SUM(salaire_base) FROM personnel")->fetchColumn();
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 border-start border-4 border-primary">
            <h6 class="text-muted small text-uppercase">Chambres</h6>
            <h3 class="fw-bold mb-0"><?= $nb_chambres ?></h3>
            <i class="bi bi-door-open position-absolute end-0 bottom-0 m-2 opacity-25 fs-1"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 border-start border-4 border-success">
            <h6 class="text-muted small text-uppercase">Clients Actifs</h6>
            <h3 class="fw-bold mb-0"><?= $nb_clients ?></h3>
            <i class="bi bi-person-badge position-absolute end-0 bottom-0 m-2 opacity-25 fs-1"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 border-start border-4 border-warning">
            <h6 class="text-muted small text-uppercase">Effectif Staff</h6>
            <h3 class="fw-bold mb-0"><?= $nb_staff ?></h3>
            <i class="bi bi-people position-absolute end-0 bottom-0 m-2 opacity-25 fs-1"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 border-start border-4 border-danger">
            <h6 class="text-muted small text-uppercase">Masse Salariale</h6>
            <h4 class="fw-bold mb-0"><?= number_format($masse_salariale ?? 0, 0, ',', ' ') ?> F</h4>
            <i class="bi bi-cash-stack position-absolute end-0 bottom-0 m-2 opacity-25 fs-1"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="alert bg-white shadow-sm border-0 d-flex align-items-center">
            <div class="flex-shrink-0 bg-warning p-3 rounded text-dark me-3">
                <i class="bi bi-megaphone-fill fs-4"></i>
            </div>
            <div>
                <h5 class="mb-1 fw-bold">Note de Service</h5>
                <p class="mb-0 text-muted small">Réunion générale prévue le lundi prochain à 10h pour le personnel de l'Hôtel OMEGA.</p>
            </div>
        </div>
    </div>
</div>
