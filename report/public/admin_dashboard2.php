<?php
$page_title = "Tableau de Bord Expert - OMEGA 2026";
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once "layout.php";

// KPI Express pour l'Expert
$total_ca = $pdo->query("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_credite_id LIKE '7%'")->fetchColumn() ?: 0;
$total_banque = $pdo->query("SELECT (SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END) - SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END)) FROM ECRITURES_COMPTABLES")->fetchColumn() ?: 0;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark">🚀 OMEGA ERP - Sénégal/UEMOA</h3>
            <p class="text-muted">Expertise Comptable & Pilotage en temps réel</p>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white p-2 shadow-sm">
                <small>Chiffre d'Affaires</small>
                <h4 class="mb-0"><?= number_format($total_ca, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white p-2 shadow-sm">
                <small>Disponibilité Banque</small>
                <h4 class="mb-0"><?= number_format($total_banque, 0, ',', ' ') ?> F</h4>
            </div>
        </div>
    </div>

    <h5 class="mb-3 border-bottom pb-2 text-primary"><i class="bi bi-pen"></i> OPÉRATIONS COURANTES</h5>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="ecriture.php" class="card p-3 shadow-sm text-decoration-none border-start border-4 border-primary">
                <h6 class="mb-1 text-dark">Saisie d'Écriture</h6>
                <small class="text-muted">Journal Général</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="ecriture_list.php" class="card p-3 shadow-sm text-decoration-none">
                <h6 class="mb-1 text-dark">Liste des Écritures</h6>
                <small class="text-muted">Consultation / Modif</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="grand_livre.php" class="card p-3 shadow-sm text-decoration-none">
                <h6 class="mb-1 text-dark">Grand Livre</h6>
                <small class="text-muted">Détail par compte</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="balance.php" class="card p-3 shadow-sm text-decoration-none">
                <h6 class="mb-1 text-dark">Balance Générale</h6>
                <small class="text-muted">Vérification Débit/Crédit</small>
            </a>
        </div>
    </div>

    <h5 class="mb-3 border-bottom pb-2 text-success"><i class="bi bi-gear"></i> MODULES DE GESTION</h5>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="immobilisations.php" class="card p-3 shadow-sm text-decoration-none bg-light">
                <h6 class="mb-1 text-dark">Immobilisations</h6>
                <small class="text-muted">Amortissements (Cl. 2)</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="stock.php" class="card p-3 shadow-sm text-decoration-none bg-light">
                <h6 class="mb-1 text-dark">Gestion des Stocks</h6>
                <small class="text-muted">Inventaire permanent</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="rapprochement.php" class="card p-3 shadow-sm text-decoration-none bg-light">
                <h6 class="mb-1 text-dark">Rapprochement Bancaire</h6>
                <small class="text-muted">Lettrage & Pointage</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="a_nouveaux.php" class="card p-3 shadow-sm text-decoration-none bg-light">
                <h6 class="mb-1 text-dark">À Nouveaux</h6>
                <small class="text-muted">Report de solde N-1</small>
            </a>
        </div>
    </div>

    <h5 class="mb-3 border-bottom pb-2 text-danger"><i class="bi bi-file-earmark-pdf"></i> ÉTATS FINANCIERS OFFICIELS</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <a href="bilan.php" class="card p-3 shadow-sm text-decoration-none border-bottom border-4 border-danger">
                <h6 class="mb-1 text-dark">Bilan Actif/Passif</h6>
                <small class="text-muted">Patrimoine de l'entreprise</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="sig.php" class="card p-3 shadow-sm text-decoration-none border-bottom border-4 border-danger">
                <h6 class="mb-1 text-dark">Tableau SIG</h6>
                <small class="text-muted">Soldes de Gestion (UEMOA)</small>
            </a>
        </div>
        <div class="col-md-4">
            <a href="flux_tresorerie.php" class="card p-3 shadow-sm text-decoration-none border-bottom border-4 border-danger">
                <h6 class="mb-1 text-dark">Flux de Trésorerie</h6>
                <small class="text-muted">Tableau de financement</small>
            </a>
        </div>
    </div>
</div>

