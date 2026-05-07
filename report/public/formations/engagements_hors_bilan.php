<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Formation - Engagements Hors Bilan";
$page_icon = "shield";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_footer.php';
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5><i class="bi bi-shield"></i> Formation : Engagements Hors Bilan (Classe 8)</h5>
            <small>Comprendre et gérer les engagements non inscrits au bilan</small>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <i class="bi bi-question-circle"></i>
                <strong>Qu'est-ce qu'un engagement hors bilan ?</strong>
                <p>Ce sont des obligations potentielles qui n'apparaissent pas au bilan mais peuvent impacter les finances.</p>
            </div>
            
            <!-- Types d'engagements -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-file-text fs-1 text-danger"></i>
                            <h6>Caution</h6>
                            <small>Garantie pour un tiers</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-bank fs-1 text-warning"></i>
                            <h6>Aval</h6>
                            <small>Garantie bancaire</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-building fs-1 text-success"></i>
                            <h6>Crédit-bail</h6>
                            <small>Location avec option d'achat</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-gavel fs-1 text-secondary"></i>
                            <h6>Litige</h6>
                            <small>Contentieux en cours</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>⚠️ Obligation légale SYSCOHADA :</strong>
                <p>Les engagements hors bilan DOIVENT être mentionnés en annexe des comptes annuels.</p>
            </div>
            
            <div class="text-center mt-4">
                <a href="../engagements_hors_bilan.php" class="btn btn-danger">Accéder au module</a>
                <a href="audit_trail.php" class="btn btn-outline-primary">Module suivant →</a>
            </div>
        </div>
    </div>
</div>
<?php include '../inc_footer.php'; ?>
