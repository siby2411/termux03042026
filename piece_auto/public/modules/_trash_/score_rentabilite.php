<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Score de rentabilité - Méthode Altman";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Calcul des indicateurs pour le score
$ca = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
$ca->execute([$exercice]);
$chiffre_affaires = $ca->fetchColumn();

$resultat = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$resultat->execute([$exercice]);
$resultat_net = $resultat->fetchColumn();

$actif_total = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 20 AND 59");
$actif_total->execute([$exercice]);
$actif_total = $actif_total->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$capitaux_propres = $capitaux_propres->fetchColumn();

$dettes_total = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 40 AND 49");
$dettes_total->execute([$exercice]);
$dettes_total = $dettes_total->fetchColumn();

$bfr = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 30 AND 49 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 40 AND 49 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$bfr->execute([$exercice]);
$bfr = $bfr->fetchColumn();

// Calcul du score Altman (version simplifiée pour PME)
$ratio1 = $actif_total > 0 ? $capitaux_propres / $actif_total : 0;           // Capitaux propres / Actif total
$ratio2 = $chiffre_affaires > 0 ? $resultat_net / $chiffre_affaires : 0;      // Rentabilité
$ratio3 = $actif_total > 0 ? ($resultat_net + 500000) / $actif_total : 0;     // Capacité de financement
$ratio4 = $capitaux_propres > 0 ? $dettes_total / $capitaux_propres : 0;       // Endettement

$score = (6.56 * $ratio1) + (3.26 * $ratio2) + (6.72 * $ratio3) + (1.05 * $ratio4);

// Interprétation du score
if ($score > 2.9) {
    $interpretation = "RISQUE DE FAILLITE TRÈS FAIBLE";
    $risque = "FAIBLE";
    $couleur = "success";
} elseif ($score > 1.8) {
    $interpretation = "RISQUE DE FAILLITE MODÉRÉ (ZONE GRISE)";
    $risque = "MOYEN";
    $couleur = "warning";
} elseif ($score > 1.1) {
    $interpretation = "RISQUE DE FAILLITE ÉLEVÉ";
    $risque = "ELEVE";
    $couleur = "danger";
} else {
    $interpretation = "RISQUE DE FAILLITE TRÈS ÉLEVÉ (ZONE CRITIQUE)";
    $risque = "CRITIQUE";
    $couleur = "dark";
}

// Sauvegarde du score
$stmt = $pdo->prepare("INSERT INTO SCORES_FINANCIERS (exercice, score_z_altman, interpretation, risque) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE score_z_altman = ?, interpretation = ?, risque = ?");
$stmt->execute([$exercice, $score, $interpretation, $risque, $score, $interpretation, $risque]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Score de rentabilité - Méthode Altman (Z-Score)</h5>
                <small>Évaluation du risque de défaillance financière</small>
            </div>
            <div class="card-body">
                
                <!-- Score principal -->
                <div class="text-center mb-4">
                    <div class="display-1 fw-bold text-<?= $couleur ?>"><?= number_format($score, 2) ?></div>
                    <h4 class="text-<?= $couleur ?>"><?= $interpretation ?></h4>
                    <div class="progress mt-3" style="height: 20px;">
                        <div class="progress-bar bg-<?= $couleur ?>" style="width: <?= min(100, ($score / 5) * 100) ?>%">
                            <?= number_format($score, 2) ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h6>Zone Sûre</h6>
                                <h4>&gt; 2.9</h4>
                                <small>Risque faible</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h6>Zone Grise</h6>
                                <h4>1.8 - 2.9</h4>
                                <small>Risque modéré</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h6>Zone de Risque</h6>
                                <h4>1.1 - 1.8</h4>
                                <small>Risque élevé</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-dark text-white text-center">
                            <div class="card-body">
                                <h6>Zone Critique</h6>
                                <h4>&lt; 1.1</h4>
                                <small>Risque très élevé</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détail du calcul -->
                <h6 class="mt-4">📊 Méthodologie de calcul du Z-Score Altman</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Indicateur</th><th>Formule</th><th>Valeur</th><th>Coefficient</th><th>Contribution</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Rentabilité financière</td>
                                <td>Capitaux propres / Actif total</td>
                                <td class="text-end"><?= number_format($ratio1 * 100, 2) ?>%</td>
                                <td class="text-end">6.56</td>
                                <td class="text-end"><?= number_format(6.56 * $ratio1, 4) ?></td>
                            </tr>
                            <tr>
                                <td>Rentabilité commerciale</td>
                                <td>Résultat net / CA</td>
                                <td class="text-end"><?= number_format($ratio2 * 100, 2) ?>%</td>
                                <td class="text-end">3.26</td>
                                <td class="text-end"><?= number_format(3.26 * $ratio2, 4) ?></td>
                            </tr>
                            <tr>
                                <td>Capacité d'autofinancement</td>
                                <td>(Résultat + Amortissements) / Actif total</td>
                                <td class="text-end"><?= number_format($ratio3 * 100, 2) ?>%</td>
                                <td class="text-end">6.72</td>
                                <td class="text-end"><?= number_format(6.72 * $ratio3, 4) ?></td>
                            </tr>
                            <tr>
                                <td>Structure financière</td>
                                <td>Dettes / Capitaux propres</td>
                                <td class="text-end"><?= number_format($ratio4, 2) ?></td>
                                <td class="text-end">1.05</td>
                                <td class="text-end"><?= number_format(1.05 * $ratio4, 4) ?></td>
                            </tr>
                            <tr class="table-primary fw-bold">
                                <td>colspan="4" class="text-end">Z-SCORE TOTAL</td>
                                <td class="text-end"><?= number_format($score, 4) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-secondary mt-3">
                    <strong>📖 Interprétation du Score :</strong><br>
                    • Score > 2.9 : Entreprise en bonne santé financière<br>
                    • 1.8 < Score < 2.9 : Zone d'incertitude, surveillance nécessaire<br>
                    • Score < 1.8 : Risque potentiel de défaillance
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
