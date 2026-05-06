<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - Variation des capitaux propres";
$page_icon = "arrow-repeat";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

// Calcul des capitaux propres
$capital = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 101")->fetchColumn();
$resultat = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn() - 
            $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$reserves = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 118 AND 119")->fetchColumn();

$capitaux_propres = $capital + $resultat + $reserves;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="bi bi-arrow-repeat"></i> Module 5 : Tableau de variation des capitaux propres</h5>
                <small>Suivi de l'évolution de votre patrimoine net</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 DÉFINITION :</strong>
                    <p>Le tableau de variation des capitaux propres retrace l'évolution de la situation nette de l'entreprise sur un exercice.</p>
                </div>
                
                <!-- Tableau de variation -->
                <h5>📊 TABLEAU DE VARIATION DES CAPITAUX PROPRES</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Rubrique</th>
                                <th>Montant (FCFA)</th>
                                <th>Variation</th>
                             </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-light">
                                <td>Capitaux propres à l'ouverture (N-1)</td>
                                <td class="text-end">0</td>
                                <td class="text-end">-</td>
                            </tr>
                            <tr>
                                <td>+ Augmentation de capital</td>
                                <td class="text-end text-success"><?= number_format($capital, 0, ',', ' ') ?></td>
                                <td class="text-end text-success">+<?= number_format($capital, 0, ',', ' ') ?></td>
                            </tr>
                            <tr>
                                 Looking for.+ Résultat de l'exercice</td>
                                <td class="text-end <?= $resultat >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($resultat), 0, ',', ' ') ?>
                                </td>
                                <td class="text-end <?= $resultat >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $resultat >= 0 ? '+' : '-' ?><?= number_format(abs($resultat), 0, ',', ' ') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>+ Affectation en réserves</td>
                                <td class="text-end text-success"><?= number_format($reserves, 0, ',', ' ') ?></td>
                                <td class="text-end text-success">+<?= number_format($reserves, 0, ',', ' ') ?></td>
                            </tr>
                            <tr class="bg-primary text-white fw-bold">
                                <td>CAPITAUX PROPRES À LA CLÔTURE</td>
                                <td class="text-end"><?= number_format($capitaux_propres, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format($capitaux_propres, 0, ',', ' ') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-success mt-3">
                    <i class="bi bi-lightbulb"></i>
                    <strong>💡 INTERPRÉTATION :</strong><br>
                    Les capitaux propres représentent la valeur nette de l'entreprise. Plus ils sont élevés, plus l'entreprise est solide financièrement.
                </div>
                
                <div class="text-center mt-4">
                    <a href="../variation_capitaux.php" class="btn btn-dark">Module complet →</a>
                    <a href="tva.php" class="btn btn-secondary">Module suivant : TVA →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
