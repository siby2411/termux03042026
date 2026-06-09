<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Contrôle interne - Audit comptable";
$page_icon = "shield";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// 1. Vérification de l'équilibre de la balance
$total_debit = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IS NOT NULL")->fetchColumn();
$total_credit = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id IS NOT NULL")->fetchColumn();
$equilibre = ($total_debit == $total_credit);

// 2. Vérification des immobilisations
$immobilisations = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (231, 241, 245, 253)")->fetchColumn();
$amortissements = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 280 AND 289")->fetchColumn();

// 3. Calcul du bilan simplifié
$actif = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (231, 241, 245, 253, 521)")->fetchColumn();
$actif = $actif - $amortissements;
$passif = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id IN (101, 112, 113, 120)")->fetchColumn();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-shield"></i> Contrôle interne - Audit comptable</h5>
                <small>Détection automatique des anomalies et non-conformités</small>
            </div>
            <div class="card-body">
                
                <!-- Synthèse des contrôles -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card <?= $equilibre ? 'bg-success' : 'bg-danger' ?> text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_debit, 0, ',', ' ') ?> F</h4>
                                <small>Total Débits / Crédits</small>
                                <?php if($equilibre): ?>
                                    <span class="badge bg-light text-dark mt-1">✓ ÉQUILIBRÉ</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark mt-1">✗ DÉSÉQUILIBRÉ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($immobilisations, 0, ',', ' ') ?> F</h4>
                                <small>Immobilisations brutes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($amortissements, 0, ',', ' ') ?> F</h4>
                                <small>Amortissements cumulés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($actif, 0, ',', ' ') ?> F</h4>
                                <small>Actif net estimé</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Équilibre balance -->
                <div class="alert <?= $equilibre ? 'alert-success' : 'alert-danger' ?>">
                    <strong>Vérification fondamentale :</strong>
                    <?php if($equilibre): ?>
                        ✅ Total des débits = Total des crédits = <?= number_format($total_debit, 0, ',', ' ') ?> F
                    <?php else: ?>
                        ❌ Déséquilibre de <?= number_format(abs($total_debit - $total_credit), 0, ',', ' ') ?> F
                    <?php endif; ?>
                </div>

                <!-- Actions correctives -->
                <div class="card border-danger mt-3">
                    <div class="card-header bg-danger text-white">🔧 Actions correctives recommandées</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Pour les immobilisations :</h6>
                                <ul>
                                    <li>Vérifier les écritures d'acquisition</li>
                                    <li>Calculer les amortissements manquants</li>
                                    <li><a href="amortissements_complet.php">→ Gérer les amortissements</a></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Pour les comptes clients :</h6>
                                <ul>
                                    <li>Lettrage des comptes clients</li>
                                    <li>Relances des impayés</li>
                                    <li><a href="lettrage_comptable.php">→ Procéder au lettrage</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <a href="fixer_bilan.php" class="btn btn-warning">🛠️ Correction automatique</a>
                            <a href="cloture_ouverture_exercice.php" class="btn btn-primary">📅 Clôture exercice</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
