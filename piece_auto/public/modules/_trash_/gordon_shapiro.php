<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Modèle de Gordon-Shapiro - Évaluation d'entreprise";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données
$resultat_net = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$resultat_net->execute([$exercice]);
$resultat = $resultat_net->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

// Nombre d'actions estimé
$nb_actions = 1000;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dividende_actuel = (float)$_POST['dividende_actuel'];
    $taux_croissance = (float)$_POST['taux_croissance'];
    $cout_capitaux = (float)$_POST['cout_capitaux'];
    $nb_actions = (int)$_POST['nb_actions'];
    
    // Modèle de Gordon-Shapiro : P0 = D0 × (1 + g) / (k - g)
    if ($cout_capitaux > $taux_croissance) {
        $dividende_futur = $dividende_actuel * (1 + $taux_croissance / 100);
        $valeur_action = $dividende_futur / (($cout_capitaux - $taux_croissance) / 100);
        $valeur_entreprise = $valeur_action * $nb_actions;
    } else {
        $valeur_action = "Non calculable (k ≤ g)";
        $valeur_entreprise = "Non calculable";
    }
    
    $resultats = [
        'dividende_actuel' => $dividende_actuel,
        'taux_croissance' => $taux_croissance,
        'cout_capitaux' => $cout_capitaux,
        'dividende_futur' => $dividende_actuel * (1 + $taux_croissance / 100),
        'valeur_action' => $valeur_action,
        'valeur_entreprise' => $valeur_entreprise,
        'nb_actions' => $nb_actions
    ];
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO GORDON_SHAPIRO (exercice, dividende_actuel, taux_croissance, cout_capitaux_propres, valeur_entreprise, valeur_action, nombre_actions) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $dividende_actuel, $taux_croissance, $cout_capitaux, 
                    is_numeric($valeur_entreprise) ? $valeur_entreprise : 0, 
                    is_numeric($valeur_action) ? $valeur_action : 0, $nb_actions]);
    
    $message = "✅ Calcul Gordon-Shapiro effectué";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Modèle de Gordon-Shapiro</h5>
                <small>Évaluation d'entreprise par actualisation des dividendes (Dividend Discount Model)</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données de base (<?= $exercice ?>)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><th>Indicateur</th><th class="text-end">Valeur</th></tr>
                                    <tr><td>Résultat net</td><td class="text-end fw-bold"><?= number_format($resultat, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Capitaux propres</td><td class="text-end fw-bold"><?= number_format($cp, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>ROE (rentabilité financière)</td><td class="text-end fw-bold"><?= $cp > 0 ? number_format($resultat / $cp * 100, 2) : 0 ?>%</td></tr>
                                    <tr><td>Bénéfice par action (estimé)</td><td class="text-end fw-bold"><?= number_format($resultat / $nb_actions, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres du modèle</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6"><label>Dividende actuel par action (F)</label>
                                        <input type="number" name="dividende_actuel" class="form-control" value="<?= number_format($resultat * 0.4 / $nb_actions, 0, '', '') ?>" step="100" required></div>
                                    <div class="col-md-6"><label>Taux de croissance des dividendes (%)</label>
                                        <input type="number" name="taux_croissance" class="form-control" value="3" step="0.5" required></div>
                                    <div class="col-md-6"><label>Coût des capitaux propres (k) %</label>
                                        <input type="number" name="cout_capitaux" class="form-control" value="<?= $cp > 0 ? number_format($resultat / $cp * 100, 2) : 12 ?>" step="0.5" required></div>
                                    <div class="col-md-6"><label>Nombre d'actions</label>
                                        <input type="number" name="nb_actions" class="form-control" value="<?= $nb_actions ?>" required></div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Calculer Gordon-Shapiro</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats du modèle Gordon-Shapiro</div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>📌 Formule :</strong> P₀ = D₁ / (k - g) où D₁ = D₀ × (1 + g)<br>
                            <strong>📌 Hypothèse :</strong> Croissance constante des dividendes à l'infini
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-primary text-white">
                                    <div class="card-body">
                                        <h6>Dividende actuel (D₀)</h6>
                                        <h4><?= number_format($resultats['dividende_actuel'], 0, ',', ' ') ?> F</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h6>Dividende futur (D₁)</h6>
                                        <h4><?= number_format($resultats['dividende_futur'], 0, ',', ' ') ?> F</h4>
                                        <small>+<?= number_format($resultats['taux_croissance'], 2) ?>% croissance</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h6>Prime de risque (k - g)</h6>
                                        <h4><?= number_format($resultats['cout_capitaux'] - $resultats['taux_croissance'], 2) ?>%</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">💰 Valeur de l'action</div>
                                    <div class="card-body text-center">
                                        <h2 class="text-success"><?= is_numeric($resultats['valeur_action']) ? number_format($resultats['valeur_action'], 0, ',', ' ') . ' F' : $resultats['valeur_action'] ?></h2>
                                        <small>Basé sur l'actualisation des dividendes futurs</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">🏢 Valeur de l'entreprise</div>
                                    <div class="card-body text-center">
                                        <h2 class="text-primary"><?= is_numeric($resultats['valeur_entreprise']) ? number_format($resultats['valeur_entreprise'], 0, ',', ' ') . ' F' : $resultats['valeur_entreprise'] ?></h2>
                                        <small><?= number_format($resultats['nb_actions'], 0, ',', ' ') ?> actions × Valeur action</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mt-3">
                            <strong>📈 Interprétation :</strong><br>
                            <?php if(is_numeric($resultats['valeur_action']) && $resultats['valeur_action'] > 0): ?>
                                La valeur théorique de l'action est de <strong><?= number_format($resultats['valeur_action'], 0, ',', ' ') ?> F</strong>.<br>
                                <?php if($resultats['valeur_action'] > ($resultat / $nb_actions)): ?>
                                    ✅ Le marché sous-évalue l'action (opportunité d'achat)
                                <?php else: ?>
                                    ⚠️ L'action est surévaluée par rapport au modèle fondamental
                                <?php endif; ?>
                            <?php else: ?>
                                ⚠️ Le modèle n'est pas applicable car le taux de croissance (g) est supérieur ou égal au coût des capitaux (k).
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
