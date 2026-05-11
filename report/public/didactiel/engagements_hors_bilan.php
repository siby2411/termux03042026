<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

$page_title = "Formation - Engagements Hors Bilan";
$page_icon = "shield";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-shield"></i> Formation : Engagements Hors Bilan (Classe 8)</h5>
                <small>Comprendre et gérer les engagements non inscrits au bilan</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Qu'est-ce qu'un engagement hors bilan ?</strong>
                    <p>Obligations potentielles qui n'apparaissent pas au bilan mais peuvent impacter les finances.</p>
                </div>

                <div class="row">
                    <div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-file-text fs-1"></i><h6>Caution</h6><small>Garantie pour un tiers</small></div></div></div>
                    <div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-bank fs-1"></i><h6>Aval</h6><small>Garantie bancaire</small></div></div></div>
                    <div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-building fs-1"></i><h6>Crédit-bail</h6><small>Location avec option d'achat</small></div></div></div>
                </div>

                <div class="alert alert-warning mt-3">
                    <strong>⚠️ Obligation légale SYSCOHADA :</strong>
                    <p>Les engagements hors bilan DOIVENT être mentionnés en annexe des comptes annuels.</p>
                </div>

                <div class="text-center mt-4">
                    <a href="../engagements_hors_bilan_complet.php" class="btn btn-danger">Accéder au module</a>
                    <a href="index.php" class="btn btn-outline-secondary">← Retour</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
