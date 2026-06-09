<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Charges et Produits - Activité ordinaire / Hors activité";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des totaux
$total_charges_courantes = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 60 AND 69 AND type_ecriture != 'EXCEPTIONNEL'")->fetchColumn();
$total_charges_exceptionnelles = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 60 AND 69 AND type_ecriture = 'EXCEPTIONNEL'")->fetchColumn();
$total_produits_courants = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 70 AND 79 AND type_ecriture != 'EXCEPTIONNEL'")->fetchColumn();
$total_produits_exceptionnels = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 70 AND 79 AND type_ecriture = 'EXCEPTIONNEL'")->fetchColumn();

// Détails par compte
$details_charges = $pdo->query("
    SELECT compte_debite_id as compte, c.intitule_compte, SUM(montant) as total
    FROM ECRITURES_COMPTABLES e
    JOIN PLAN_COMPTABLE_UEMOA c ON e.compte_debite_id = c.compte_id
    WHERE compte_debite_id BETWEEN 60 AND 69
    GROUP BY compte_debite_id
    ORDER BY compte_debite_id
")->fetchAll();

$details_produits = $pdo->query("
    SELECT compte_credite_id as compte, c.intitule_compte, SUM(montant) as total
    FROM ECRITURES_COMPTABLES e
    JOIN PLAN_COMPTABLE_UEMOA c ON e.compte_credite_id = c.compte_id
    WHERE compte_credite_id BETWEEN 70 AND 79
    GROUP BY compte_credite_id
    ORDER BY compte_credite_id
")->fetchAll();

$resultat_courant = $total_produits_courants - $total_charges_courantes;
$resultat_exceptionnel = $total_produits_exceptionnels - $total_charges_exceptionnelles;
$resultat_net = $resultat_courant + $resultat_exceptionnel;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Charges et Produits - Analyse SYSCOHADA</h5>
                <small>Distinction activité ordinaire / hors activité ordinaire</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Critères de distinction SYSCOHADA :</strong><br>
                    • <strong>Activité ordinaire</strong> : Opérations courantes, récurrentes (Classe 60-69 pour charges, 70-79 pour produits)<br>
                    • <strong>Hors activité ordinaire</strong> : Événements rares, exceptionnels (cessions, sinistres, pénalités)
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-danger text-white">
                            <div class="card-header">⚠️ CHARGES</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <h6>Activité ordinaire</h6>
                                        <h3><?= number_format($total_charges_courantes, 0, ',', ' ') ?> F</h3>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h6>Hors activité</h6>
                                        <h3 class="text-warning"><?= number_format($total_charges_exceptionnelles, 0, ',', ' ') ?> F</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-header">📈 PRODUITS</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <h6>Activité ordinaire</h6>
                                        <h3><?= number_format($total_produits_courants, 0, ',', ' ') ?> F</h3>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h6>Hors activité</h6>
                                        <h3 class="text-warning"><?= number_format($total_produits_exceptionnels, 0, ',', ' ') ?> F</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détail Charges -->
                <ul class="nav nav-tabs" id="cpTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#charges">📉 Charges détaillées (Classe 6)</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#produits">📈 Produits détaillés (Classe 7)</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#caspratique">📋 Cas pratique</button></li>
                </ul>
                
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="charges">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Compte</th><th>Intitulé</th><th class="text-end">Montant (FCFA)</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($details_charges as $c): ?>
                                    <tr>
                                        <td class="text-center"><?= $c['compte'] ?></td>
                                        <td><?= htmlspecialchars($c['intitule_compte']) ?></td>
                                        <td class="text-end"><?= number_format($c['total'], 0, ',', ' ') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-danger fw-bold"><td colspan="2" class="text-end">TOTAL CHARGES :</td><td class="text-end"><?= number_format($total_charges_courantes + $total_charges_exceptionnelles, 0, ',', ' ') ?> F</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="produits">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Compte</th><th>Intitulé</th><th class="text-end">Montant (FCFA)</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($details_produits as $p): ?>
                                    <tr>
                                        <td class="text-center"><?= $p['compte'] ?></td>
                                        <td><?= htmlspecialchars($p['intitule_compte']) ?></td>
                                        <td class="text-end"><?= number_format($p['total'], 0, ',', ' ') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-success fw-bold"><td colspan="2" class="text-end">TOTAL PRODUITS :</td><td class="text-end"><?= number_format($total_produits_courants + $total_produits_exceptionnels, 0, ',', ' ') ?> F</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="caspratique">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📋 Cas pratique - Comptabilisation charges/produits</div>
                            <div class="card-body">
                                <h6>⚠️ Charges d'activité ordinaire :</h6>
                                <table class="table table-sm">
                                    <tr><td>Achat de marchandises</td><td class="text-end">601 → Débit</td><td class="text-end">521 → Crédit</td><td class="text-end">500.000 F</td></tr>
                                    <tr><td>Salaires du personnel</td><td class="text-end">661 → Débit</td><td class="text-end">421 → Crédit</td><td class="text-end">3.500.000 F</td></tr>
                                </table>
                                
                                <h6 class="mt-3">📈 Produits d'activité ordinaire :</h6>
                                <table class="table table-sm">
                                    <tr><td>Ventes de marchandises</td><td class="text-end">521 → Débit</td><td class="text-end">701 → Crédit</td><td class="text-end">5.000.000 F</td></tr>
                                    <tr><td>Prestations de services</td><td class="text-end">521 → Débit</td><td class="text-end">703 → Crédit</td><td class="text-end">2.500.000 F</td></tr>
                                </table>
                                
                                <h6 class="mt-3">⚠️ Charges hors activité ordinaire :</h6>
                                <table class="table table-sm">
                                    <tr><td>Perte sur cession d'immobilisation</td><td class="text-end">675 → Débit</td><td class="text-end">521 → Crédit</td><td class="text-end">200.000 F</td></tr>
                                    <tr><td>Pénalités fiscales</td><td class="text-end">668 → Débit</td><td class="text-end">521 → Crédit</td><td class="text-end">150.000 F</td></tr>
                                </table>
                                
                                <h6 class="mt-3">📈 Produits hors activité ordinaire :</h6>
                                <table class="table table-sm">
                                    <tr><td>Plus-value sur cession d'immobilisation</td><td class="text-end">521 → Débit</td><td class="text-end">775 → Crédit</td><td class="text-end">300.000 F</td></tr>
                                    <tr><td>Quote-part subvention</td><td class="text-end">521 → Débit</td><td class="text-end">777 → Crédit</td><td class="text-end">1.000.000 F</td></tr>
                                </table>
                                
                                <div class="alert alert-success mt-3">
                                    <strong>✅ RÉSULTAT NET :</strong><br>
                                    (Produits ordinaires + Exceptionnels) - (Charges ordinaires + Exceptionnelles)<br>
                                    = <strong><?= number_format($resultat_net, 0, ',', ' ') ?> FCFA</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
