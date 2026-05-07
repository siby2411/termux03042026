<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Ratios financiers";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="bi bi-graph-up"></i> Module : Les Ratios Financiers</h5>
                <small>Analyser la santé financière de l'entreprise</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>Un ratio est un indicateur qui permet d'analyser la performance, la liquidité ou la solvabilité d'une entreprise.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-primary text-white">📊 Ratios de liquidité</div>
                            <div class="card-body">
                                <p><strong>Liquidité générale</strong> = Actif circulant / Passif circulant (seuil: >1)</p>
                                <p><strong>Liquidité réduite</strong> = (Actif - Stocks) / Passif (seuil: >0.5)</p>
                                <p><strong>Liquidité immédiate</strong> = Disponibilités / Passif (seuil: >0.2)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-success text-white">💰 Ratios de rentabilité</div>
                            <div class="card-body">
                                <p><strong>Rentabilité économique</strong> = Résultat / Actif total (seuil: >5%)</p>
                                <p><strong>Rentabilité financière</strong> = Résultat / Capitaux propres (seuil: >10%)</p>
                                <p><strong>Taux de marge</strong> = Résultat / CA (seuil: >5%)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-warning text-dark">🏗️ Ratios de structure</div>
                            <div class="card-body">
                                <p><strong>Autonomie financière</strong> = Capitaux propres / Total actif (seuil: >50%)</p>
                                <p><strong>Endettement</strong> = Dettes / Capitaux propres (seuil: <100%)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-danger text-white">⏱️ Ratios de gestion</div>
                            <div class="card-body">
                                <p><strong>Rotation des stocks</strong> = Achats / Stock moyen</p>
                                <p><strong>Délai paiement fournisseurs</strong> = (Fournisseurs / Achats) × 360</p>
                                <p><strong>Délai encaissement clients</strong> = (Clients / CA) × 360</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../ratios_financiers.php" class="btn btn-dark">Voir vos ratios calculés →</a>
                    <a href="sig.php" class="btn btn-secondary">← Module précédent</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
