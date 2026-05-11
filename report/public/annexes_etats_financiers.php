<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Annexes aux États Financiers";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données pour les annexes
// 1. Engagements hors bilan
$engagements = $pdo->query("SELECT * FROM ENGAGEMENTS_HORS_BILAN WHERE statut = 'ACTIF'")->fetchAll();
$total_engagements = array_sum(array_column($engagements, 'montant'));

// 2. Échéancier des dettes
$dettes = $pdo->prepare("SELECT compte_credite_id, SUM(montant) as total FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 40 AND 49 AND YEAR(date_ecriture) = ? GROUP BY compte_credite_id");
$dettes->execute([$exercice]);
$dettes_data = $dettes->fetchAll();

// 3. Échéancier des créances
$creances = $pdo->prepare("SELECT compte_debite_id, SUM(montant) as total FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 40 AND 49 AND YEAR(date_ecriture) = ? GROUP BY compte_debite_id");
$creances->execute([$exercice]);
$creances_data = $creances->fetchAll();

// 4. Effectif du personnel
$nb_salaries = $pdo->query("SELECT COUNT(*) FROM SALARIES WHERE statut = 'ACTIF'")->fetchColumn();

// 5. Honoraires des commissaires aux comptes
$honoraires = $pdo->prepare("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 622 AND YEAR(date_ecriture) = ?");
$honoraires->execute([$exercice]);
$total_honoraires = $honoraires->fetchColumn() ?: 0;

// 6. Rémunérations des dirigeants
$remuneration_dirigeants = $pdo->prepare("SELECT SUM(montant) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 641 AND YEAR(date_ecriture) = ?");
$remuneration_dirigeants->execute([$exercice]);
$total_remuneration = $remuneration_dirigeants->fetchColumn() ?: 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> Annexes aux États Financiers - Exercice <?= $exercice ?></h5>
                <small>Informations complémentaires requises par SYSCOHADA</small>
            </div>
            <div class="card-body">
                
                <!-- 1. Engagements hors bilan -->
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">1. Engagements hors bilan (Classe 8)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Type</th><th>Bénéficiaire</th><th>Montant</th><th>Échéance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($engagements as $e): ?>
                                    <tr>
                                        <td><?= $e['date_engagement'] ?></td>
                                        <td><?= $e['type'] ?></td>
                                        <td><?= htmlspecialchars($e['beneficiaire']) ?></td>
                                        <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> <?= $e['devise'] ?></td>
                                        <td><?= $e['date_echeance'] ?? '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary"><td colspan="3" class="fw-bold">TOTAL ENGAGEMENTS</td>
                                    <td class="text-end fw-bold"><?= number_format($total_engagements, 0, ',', ' ') ?> F</td><td></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. Échéancier des dettes -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">2. Échéancier des dettes (Fournisseurs, fiscales, sociales)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light"><tr><th>Compte</th><th>Intitulé</th><th class="text-end">Montant (F)</th></tr></thead>
                                <tbody>
                                    <?php foreach($dettes_data as $d): ?>
                                    <tr>
                                        <td class="text-center"><?= $d['compte_credite_id'] ?></td>
                                        <td><?php $stmt = $pdo->prepare("SELECT intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = ?"); $stmt->execute([$d['compte_credite_id']]); echo htmlspecialchars($stmt->fetchColumn() ?: '-'); ?></td>
                                        <td class="text-end"><?= number_format($d['total'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 3. Échéancier des créances -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">3. Échéancier des créances (Clients)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light"><tr><th>Compte</th><th>Intitulé</th><th class="text-end">Montant (F)</th></tr></thead>
                                <tbody>
                                    <?php foreach($creances_data as $c): ?>
                                    <tr>
                                        <td class="text-center"><?= $c['compte_debite_id'] ?></td>
                                        <td><?php $stmt = $pdo->prepare("SELECT intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = ?"); $stmt->execute([$c['compte_debite_id']]); echo htmlspecialchars($stmt->fetchColumn() ?: '-'); ?></td>
                                        <td class="text-end"><?= number_format($c['total'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. Informations sur le personnel -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">4. Informations sur le personnel</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Effectif moyen du personnel :</strong> <?= $nb_salaries ?> employés</p>
                                <p><strong>Masse salariale annuelle :</strong> <?= number_format($total_remuneration, 0, ',', ' ') ?> FCFA</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Honoraires des CAC :</strong> <?= number_format($total_honoraires, 0, ',', ' ') ?> FCFA</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Événements postérieurs à la clôture -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">5. Événements postérieurs à la clôture</div>
                    <div class="card-body">
                        <p>Aucun événement significatif à signaler.</p>
                        <a href="evenements_posterieurs.php" class="btn btn-sm btn-outline-secondary">Gérer les événements</a>
                    </div>
                </div>

                <!-- 6. Tableau des filiales et participations -->
                <div class="card">
                    <div class="card-header bg-dark text-white">6. Filiales et participations</div>
                    <div class="card-body">
                        <p>Aucune participation significative.</p>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Note :</strong> Ces annexes font partie intégrante des états financiers et doivent être lues conjointement avec le bilan, le compte de résultat et le tableau des flux de trésorerie.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
