<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Ratios Financiers - Analyse Performance";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Mise à jour automatique des ratios depuis les écritures
$exercice_courant = date('Y');

// Appel de la procédure stockée pour calcul automatique
try {
    $pdo->exec("CALL calculer_ratios_automatique($exercice_courant)");
    $message = "✅ Ratios calculés automatiquement à partir des écritures";
} catch (Exception $e) {
    // Table peut-être vide, ignorer l'erreur
}

// Récupération des ratios calculés
$ratios = $pdo->prepare("SELECT * FROM RATIOS_FINANCIERS WHERE exercice = ? ORDER BY exercice DESC LIMIT 1");
$ratios->execute([$exercice_courant]);
$ratio = $ratios->fetch();

// Récupération des données de base
$total_produits = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$total_charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$resultat_net = $total_produits - $total_charges;
$capitaux_propres = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 101 AND 199")->fetchColumn();

// Sauvegarde manuelle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO RATIOS_FINANCIERS (exercice, date_calcul, ratio_liquidite_generale, ratio_liquidite_reduite, ratio_dettes_capitaux, ratio_autonomie_financiere, rentabilite_economique, rentabilite_financiere, besoin_fonds_roulement, fonds_roulement, tresorerie_nette) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ratio_liquidite_generale = VALUES(ratio_liquidite_generale), ratio_liquidite_reduite = VALUES(ratio_liquidite_reduite), ratio_dettes_capitaux = VALUES(ratio_dettes_capitaux), ratio_autonomie_financiere = VALUES(ratio_autonomie_financiere), rentabilite_economique = VALUES(rentabilite_economique), rentabilite_financiere = VALUES(rentabilite_financiere), besoin_fonds_roulement = VALUES(besoin_fonds_roulement), fonds_roulement = VALUES(fonds_roulement), tresorerie_nette = VALUES(tresorerie_nette)");
        $stmt->execute([
            $_POST['exercice'],
            $_POST['ratio_liquidite_generale'],
            $_POST['ratio_liquidite_reduite'],
            $_POST['ratio_dettes_capitaux'],
            $_POST['ratio_autonomie_financiere'],
            $_POST['rentabilite_economique'],
            $_POST['rentabilite_financiere'],
            $_POST['besoin_fonds_roulement'],
            $_POST['fonds_roulement'],
            $_POST['tresorerie_nette']
        ]);
        $message = "✅ Ratios enregistrés avec succès";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Analyse des Ratios Financiers</h5>
                <small>Évaluation de la performance financière - SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <!-- Indicateurs synthétiques -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card-stats text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="bi bi-cash-stack fs-2 text-primary"></i>
                            </div>
                            <h3><?= number_format($total_produits, 0, ',', ' ') ?> F</h3>
                            <small>Chiffre d'Affaires</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stats text-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="bi bi-box-seam fs-2 text-danger"></i>
                            </div>
                            <h3><?= number_format($total_charges, 0, ',', ' ') ?> F</h3>
                            <small>Total Charges</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stats text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="bi bi-trophy fs-2 text-success"></i>
                            </div>
                            <h3 class="<?= $resultat_net >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format(abs($resultat_net), 0, ',', ' ') ?> F
                            </h3>
                            <small>Résultat Net</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card-stats text-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 d-inline-block">
                                <i class="bi bi-building fs-2 text-info"></i>
                            </div>
                            <h3><?= number_format($capitaux_propres, 0, ',', ' ') ?> F</h3>
                            <small>Capitaux Propres</small>
                        </div>
                    </div>
                </div>
                
                <!-- Tableau des ratios -->
                <h6 class="mt-4"><i class="bi bi-table"></i> Détail des Ratios Financiers</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Ratio</th>
                                <th>Valeur</th>
                                <th>Interprétation</th>
                                <th>Seuil optimal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Ratio de liquidité générale</strong><br><small>Actif circulant / Passif circulant</small></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['ratio_liquidite_generale'] ?? 0, 2) ?></td>
                                <td><?= ($ratio['ratio_liquidite_generale'] ?? 0) >= 1 ? '✅ Bonne couverture' : '⚠️ Risque de liquidité' ?></td>
                                <td class="text-center">≥ 1</td>
                            </tr>
                            <tr>
                                <td><strong>Ratio de liquidité réduite</strong><br><small>(Actif - Stocks) / Passif</small></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['ratio_liquidite_reduite'] ?? 0, 2) ?></td>
                                <td><?= ($ratio['ratio_liquidite_reduite'] ?? 0) >= 0.5 ? '✅ Suffisant' : '⚠️ Insuffisant' ?></td>
                                <td class="text-center">≥ 0.5</td>
                            </tr>
                            <tr>
                                <td><strong>Ratio d'autonomie financière</strong><br><small>Capitaux propres / Total actif</small></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['ratio_autonomie_financiere'] ?? 0, 2) ?>%</td>
                                <td><?= ($ratio['ratio_autonomie_financiere'] ?? 0) >= 50 ? '✅ Bonne autonomie' : '⚠️ Dépendance financière' ?></td>
                                <td class="text-center">≥ 50%</td>
                            </tr>
                            <tr>
                                <td><strong>Rentabilité économique (ROE)</strong><br><small>Résultat net / Actif total</small></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['rentabilite_economique'] ?? 0, 2) ?>%</td>
                                <td><?= ($ratio['rentabilite_economique'] ?? 0) > 5 ? '✅ Rentable' : '⚠️ Peu rentable' ?></td>
                                <td class="text-center">&gt; 5%</td>
                            </tr>
                            <tr>
                                <td><strong>Rentabilité financière (ROA)</strong><br><small>Résultat net / Capitaux propres</small></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['rentabilite_financiere'] ?? 0, 2) ?>%</td>
                                <td><?= ($ratio['rentabilite_financiere'] ?? 0) > 10 ? '✅ Excellente' : '⚠️ À améliorer' ?></td>
                                <td class="text-center">&gt; 10%</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="bg-light fw-bold">📊 INDICATEURS DE GESTION</td>
                            </tr>
                            <tr>
                                <td><strong>Besoin en Fonds de Roulement (BFR)</strong></td>
                                <td class="text-center fw-bold text-<?= ($ratio['besoin_fonds_roulement'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                                    <?= number_format(abs($ratio['besoin_fonds_roulement'] ?? 0), 0, ',', ' ') ?> F
                                </td>
                                <td><?= ($ratio['besoin_fonds_roulement'] ?? 0) > 0 ? 'Besoin de financement CT' : 'Ressource excédentaire' ?></td>
                                <td class="text-center">Minimiser</td>
                            </tr>
                            <tr>
                                <td><strong>Fonds de Roulement (FR)</strong></td>
                                <td class="text-center fw-bold"><?= number_format($ratio['fonds_roulement'] ?? 0, 0, ',', ' ') ?> F</td>
                                <td><?= ($ratio['fonds_roulement'] ?? 0) > 0 ? 'Marge de sécurité' : 'Insuffisance de ressources' ?></td>
                                <td class="text-center">&gt; 0</td>
                            </tr>
                            <tr>
                                <td><strong>Trésorerie Nette</strong></td>
                                <td class="text-center fw-bold text-<?= ($ratio['tresorerie_nette'] ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                    <?= number_format(abs($ratio['tresorerie_nette'] ?? 0), 0, ',', ' ') ?> F
                                </td>
                                <td><?= ($ratio['tresorerie_nette'] ?? 0) >= 0 ? 'Trésorerie positive' : 'Découvert bancaire' ?></td>
                                <td class="text-center">&gt; 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Guide d'interprétation -->
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 LEXIQUE DES INDICATEURS :</strong><br>
                    • <strong>BFR (Besoin en Fonds de Roulement)</strong> : Montant nécessaire pour financer le cycle d'exploitation<br>
                    • <strong>FR (Fonds de Roulement)</strong> : Ressources stables disponibles (>0 = bonne santé)<br>
                    • <strong>Trésorerie Nette</strong> = FR - BFR (disponibilités réelles)
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
