<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Dashboard Complet - SYSCOHADA UEMOA";
$page_icon = "speedometer2";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Indicateurs globaux
$nb_tiers = $pdo->query("SELECT COUNT(*) FROM TIERS")->fetchColumn();
$nb_engagements = $pdo->query("SELECT COUNT(*) FROM ENGAGEMENTS_HORS_BILAN WHERE statut = 'ACTIF'")->fetchColumn();
$total_engagements = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ENGAGEMENTS_HORS_BILAN WHERE statut = 'ACTIF'")->fetchColumn();
$nb_audit = $pdo->query("SELECT COUNT(*) FROM AUDIT_TRAIL WHERE DATE(date_action) = CURDATE()")->fetchColumn();

// Indicateurs comptables
$total_ca = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$total_charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$resultat = $total_ca - $total_charges;
?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <h5><i class="bi bi-check-circle-fill"></i> OMEGA CONSULTING ERP - Solution SYSCOHADA Complète</h5>
            <small>Conformité totale avec les normes OHADA/UEMOA</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-people fs-2"></i>
                <h3><?= $nb_tiers ?></h3>
                <small>Tiers enregistrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="bi bi-shield fs-2"></i>
                <h3><?= $nb_engagements ?></h3>
                <small>Engagements hors bilan</small>
                <small class="d-block"><?= number_format($total_engagements, 0, ',', ' ') ?> F</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-eye fs-2"></i>
                <h3><?= $nb_audit ?></h3>
                <small>Actions aujourd'hui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="bi bi-calculator fs-2"></i>
                <h3><?= number_format($resultat, 0, ',', ' ') ?> F</h3>
                <small>Résultat net</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">📋 Modules disponibles</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="tiers.php">Gestion des tiers</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="facturation.php">Facturation</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="engagements_hors_bilan.php">Engagements hors bilan</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="audit_trail.php">Audit trail</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="declarations_fiscales.php">Déclarations fiscales</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="ecriture.php">Écritures comptables</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="bilan.php">Bilan</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="sig.php">Tableau SIG</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="ratios_financiers.php">Ratios financiers</a></li>
                            <li><i class="bi bi-check-circle-fill text-success"></i> <a href="didactiel/">Didacticiel</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">📊 Conformité SYSCOHADA</div>
            <div class="card-body">
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">Plan comptable UEMOA</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">États financiers</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">Amortissements</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">Régularisations</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">Engagements hors bilan (Classe 8)</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: 100%">Audit trail</div>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar bg-primary" style="width: 100%">100% Conforme OHADA</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
