<?php
$page_title = "Dashboard - SynthesePro";


require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../includes/auth_check.php'); // si besoin



require_once "layout.php";

?>



<style>
.card-hover:hover {
    transform: translateY(-5px);
    transition: 0.3s ease;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}
.icon-big {
    font-size: 35px;
    opacity: 0.85;
}
</style>

<div class="container-fluid">

    <!-- TITRE -->
    <div class="mb-4">
        <h3 class="fw-bold">Tableau de bord</h3>
        <p class="text-muted">Aperçu global – États financiers & modules de gestion</p>
    </div>

    <!-- --------------------------- -->
    <!-- 1️⃣ ETATS FINANCIERS -->
    <!-- --------------------------- -->
    <h5 class="mt-4 mb-3">📊 États Financiers</h5>

    <div class="row g-4">

        <div class="col-md-3">
            <a href="bilan.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-primary"><i class="bi bi-columns-gap"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Bilan</h6>
                            <small class="text-muted">Actif / Passif</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="resultat.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-success"><i class="bi bi-graph-up"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Compte de Résultat</h6>
                            <small class="text-muted">Produits / Charges</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="balance.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-warning"><i class="bi bi-table"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Balance</h6>
                            <small class="text-muted">Débit / Crédit</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="flux_tresorerie.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-info"><i class="bi bi-cash-coin"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Flux de trésorerie</h6>
                            <small class="text-muted">Cash-flow</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="sig.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-secondary"><i class="bi bi-file-earmark-spreadsheet"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">SIG</h6>
                            <small class="text-muted">Soldes intermédiaires</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>


    <!-- --------------------------- -->
    <!-- 2️⃣ MODULES DE GESTION -->
    <!-- --------------------------- -->
    <h5 class="mt-5 mb-3">🧩 Modules de Gestion</h5>

    <div class="row g-4">

        <!-- Rapprochement bancaire -->
        <div class="col-md-3">
            <a href="rapprochement.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-primary"><i class="bi bi-bank"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Rapprochement Bancaire</h6>
                            <small class="text-muted">Banque vs Comptabilité</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Variation capitaux -->
        <div class="col-md-3">
            <a href="variation_capitaux.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-dark"><i class="bi bi-people"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Variation Capitaux Propres</h6>
                            <small class="text-muted">Évolution du capital</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Contrôle budgétaire -->
        <div class="col-md-3">
            <a href="controle_budget.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-warning"><i class="bi bi-wallet2"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Contrôle Budgétaire</h6>
                            <small class="text-muted">Budget vs Réel</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Contrôle de gestion -->
        <div class="col-md-3">
            <a href="controle_gestion.php" class="text-decoration-none">
                <div class="card p-3 shadow-sm card-hover">
                    <div class="d-flex align-items-center">
                        <div class="icon-big text-success"><i class="bi bi-speedometer2"></i></div>
                        <div class="ms-3">
                            <h6 class="fw-bold mb-0">Contrôle de Gestion</h6>
                            <small class="text-muted">Indicateurs & KPI</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>

</div>

