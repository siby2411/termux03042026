<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$page_title = "Gestion TVA - Sénégal";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Calcul TVA collectée (classe 7 - 18%)
$tva_collectee = $pdo->query("SELECT SUM(montant) * 0.18 FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 70 AND 79")->fetchColumn();

// Calcul TVA déductible (classe 6 - 18%)
$tva_deductible = $pdo->query("SELECT SUM(montant) * 0.18 FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 60 AND 69")->fetchColumn();

$tva_a_payer = $tva_collectee - $tva_deductible;
?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-percent"></i> Déclaration TVA - Sénégal (Taux 18%)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-info text-center">
                            <small>TVA COLLECTÉE (18%)</small>
                            <h3 class="text-primary"><?= number_format($tva_collectee, 0, ',', ' ') ?> F</h3>
                            <small>Sur ventes et prestations</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-warning text-center">
                            <small>TVA DÉDUCTIBLE (18%)</small>
                            <h3 class="text-warning"><?= number_format($tva_deductible, 0, ',', ' ') ?> F</h3>
                            <small>Sur achats et charges</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert <?= $tva_a_payer > 0 ? 'alert-danger' : 'alert-success' ?> text-center">
                            <small>TVA À PAYER / CRÉDIT</small>
                            <h3><?= number_format(abs($tva_a_payer), 0, ',', ' ') ?> F</h3>
                            <small><?= $tva_a_payer > 0 ? 'À payer au fisc' : 'Crédit TVA récupérable' ?></small>
                        </div>
                    </div>
                </div>
                <div class="alert alert-secondary mt-3">
                    <strong><i class="bi bi-info-circle"></i> Échéances TVA Sénégal :</strong><br>
                    - Déclaration mensuelle (avant le 15 du mois suivant)<br>
                    - Téléprocédure via e-TVA (Direction Générale des Impôts)
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
