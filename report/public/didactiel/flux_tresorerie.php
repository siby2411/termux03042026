<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Flux de trésorerie";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

$tresorerie = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) FROM ECRITURES_COMPTABLES")->fetchColumn();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5><i class="bi bi-cash-stack"></i> Module : Tableau des Flux de Trésorerie</h5>
                <small>Comprendre les mouvements de liquidités</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>Le tableau des flux de trésorerie retrace l'origine et l'utilisation des liquidités sur l'exercice.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <i class="bi bi-arrow-up-circle fs-1"></i>
                                <h5>Flux d'exploitation</h5>
                                <small>Activité courante</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <i class="bi bi-building fs-1"></i>
                                <h5>Flux d'investissement</h5>
                                <small>Acquisitions/cessions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <i class="bi bi-bank fs-1"></i>
                                <h5>Flux de financement</h5>
                                <small>Capital, emprunts</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-secondary mt-4">
                    <strong>🔑 Relation fondamentale :</strong><br>
                    <code>Trésorerie finale = Trésorerie initiale + Flux exploitation + Flux investissement + Flux financement</code>
                </div>
                
                <div class="text-center mt-3">
                    <h4>Votre trésorerie actuelle (compte 521) :</h4>
                    <h2 class="<?= $tresorerie >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format(abs($tresorerie), 0, ',', ' ') ?> FCFA
                    </h2>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../flux_tresorerie.php" class="btn btn-info">Voir votre tableau des flux →</a>
                    <a href="../previsions_budgetaires.php" class="btn btn-primary">Module suivant : Prévisions →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
