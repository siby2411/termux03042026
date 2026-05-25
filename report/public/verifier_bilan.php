<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Vérificateur de bilan automatique";
$page_icon = "check-circle";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$erreurs = [];

// 1. Vérification de l'équilibre général
$total_debit = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IS NOT NULL")->fetchColumn();
$total_credit = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id IS NOT NULL")->fetchColumn();

if($total_debit != $total_credit) {
    $erreurs[] = "❌ Déséquilibre général : Débit = " . number_format($total_debit, 0, ',', ' ') . " F, Crédit = " . number_format($total_credit, 0, ',', ' ') . " F";
}

// 2. Vérification des immobilisations
$immobilisations_brutes = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 20 AND 27")->fetchColumn();
$amortissements = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 280 AND 289")->fetchColumn();

if($immobilisations_brutes == 0 && $amortissements > 0) {
    $erreurs[] = "⚠️ Immobilisations brutes nulles mais amortissements existants (" . number_format($amortissements, 0, ',', ' ') . " F)";
}

// 3. Vérification de la trésorerie
$tresorerie = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 521")->fetchColumn();
$tresorerie_credit = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 521")->fetchColumn();

if($tresorerie < 0) {
    $erreurs[] = "⚠️ Découvert bancaire non autorisé : Trésorerie = " . number_format($tresorerie, 0, ',', ' ') . " F";
}

// 4. Vérification des comptes de charges/produits non clôturés
$produits_non_clotures = $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799 AND type_ecriture != 'CLOTURE'")->fetchColumn();
$charges_non_clotures = $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699 AND type_ecriture != 'CLOTURE'")->fetchColumn();

if($produits_non_clotures > 0 || $charges_non_clotures > 0) {
    $erreurs[] = "⚠️ Comptes de gestion non clôturés : Produits = $produits_non_clotures, Charges = $charges_non_clotures";
}

// 5. Calcul du bilan
$actif = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 20 AND 59")->fetchColumn();
$passif = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 100 AND 499")->fetchColumn();

if(abs($actif - $passif) > 1) {
    $erreurs[] = "❌ Bilan non équilibré : Actif = " . number_format($actif, 0, ',', ' ') . " F, Passif = " . number_format($passif, 0, ',', ' ') . " F";
}

// 6. Vérification des comptes sans mouvement
$comptes_inactifs = $pdo->query("
    SELECT c.compte_id, c.intitule_compte
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id)
    WHERE c.compte_id BETWEEN 100 AND 799
    GROUP BY c.compte_id
    HAVING COUNT(e.id) = 0
    LIMIT 10
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-check-circle"></i> Vérificateur automatique de bilan</h5>
                <small>Détecte les anomalies avant qu'elles n'impactent le bilan</small>
            </div>
            <div class="card-body">
                
                <!-- Synthèse -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_debit, 0, ',', ' ') ?> F</h4>
                                <small>Total Débits</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_credit, 0, ',', ' ') ?> F</h4>
                                <small>Total Crédits</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($actif, 0, ',', ' ') ?> F</h4>
                                <small>Actif total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($passif, 0, ',', ' ') ?> F</h4>
                                <small>Passif total</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des erreurs -->
                <?php if(empty($erreurs)): ?>
                    <div class="alert alert-success text-center">
                        ✅ Aucune anomalie détectée - Bilan équilibré !
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <strong>⚠️ <?= count($erreurs) ?> anomalie(s) détectée(s) :</strong>
                        <ul class="mt-2 mb-0">
                            <?php foreach($erreurs as $e): ?>
                                <li><?= $e ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Comptes inactifs -->
                <?php if(!empty($comptes_inactifs)): ?>
                <div class="card mt-3">
                    <div class="card-header bg-secondary text-white">Comptes sans mouvement</div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach($comptes_inactifs as $c): ?>
                            <div class="col-md-3">
                                <span class="badge bg-secondary"><?= $c['compte_id'] ?></span> <?= htmlspecialchars($c['intitule_compte']) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions correctives -->
                <div class="alert alert-info mt-3">
                    <strong>🔧 Actions recommandées :</strong>
                    <ul class="mb-0">
                        <li><a href="controle_interne.php">📊 Lancer un audit complet</a></li>
                        <li><a href="cloture_ouverture_exercice.php">📅 Exécuter la clôture d'exercice</a></li>
                        <li><a href="fixer_bilan.php">🛠️ Corriger automatiquement</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
