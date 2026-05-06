<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Didacticiel - SIG & Ratios financiers";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/../config/config.php';
include '../inc_navbar.php';

// Calcul automatique du SIG à partir des écritures
$ca = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$achats = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$charges_personnel = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 661 AND 669")->fetchColumn();
$autres_charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 63 AND 65")->fetchColumn();

$marge_commerciale = $ca - $achats;
$ebe = $marge_commerciale - $charges_personnel;
$resultat_exploitation = $ebe - $autres_charges;
$capacite_autofinancement = $resultat_exploitation;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5><i class="bi bi-graph-up"></i> Module 3 : SIG - Soldes Intermédiaires de Gestion</h5>
                <small>Les indicateurs clés de performance de votre entreprise</small>
            </div>
            <div class="card-body">
                
                <!-- Explication EBE -->
                <div class="alert alert-primary">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📊 QU'EST-CE QUE L'EBE (Excédent Brut d'Exploitation) ?</strong>
                    <p class="mt-2">L'EBE est la richesse générée par l'activité courante de l'entreprise, avant prise en compte des amortissements, provisions et frais financiers.</p>
                    <div class="bg-white p-2 rounded">
                        <strong>🔢 Formule de calcul :</strong><br>
                        <code>EBE = Ventes - Achats - Charges de personnel</code>
                    </div>
                </div>
                
                <!-- Explication CAF -->
                <div class="alert alert-success">
                    <i class="bi bi-calculator-fill"></i>
                    <strong>💰 CAF (Capacité d'Autofinancement) - NOUVEAU MODULE CALCULÉ</strong>
                    <p class="mt-2">La CAF mesure la capacité de l'entreprise à générer des ressources internes pour financer ses investissements.</p>
                    <div class="bg-white p-2 rounded">
                        <strong>🔢 Formule de calcul :</strong><br>
                        <code>CAF = Résultat net + Dotations aux amortissements + Provisions - Reprises</code>
                    </div>
                    <div class="mt-2">
                        <strong>💡 Votre CAF calculée :</strong>
                        <h3 class="text-success"><?= number_format($capacite_autofinancement, 0, ',', ' ') ?> FCFA</h3>
                    </div>
                </div>
                
                <!-- Explication BFR -->
                <div class="alert alert-warning">
                    <i class="bi bi-arrow-left-right"></i>
                    <strong>🔄 BFR (Besoin en Fonds de Roulement) - NOUVEAU MODULE</strong>
                    <p class="mt-2">Le BFR représente le décalage entre les encaissements des clients et les décaissements aux fournisseurs.</p>
                    <div class="bg-white p-2 rounded">
                        <strong>🔢 Formule de calcul :</strong><br>
                        <code>BFR = Créances clients + Stocks - Dettes fournisseurs</code>
                    </div>
                    <div class="mt-2">
                        <strong>📌 Interprétation :</strong><br>
                        - BFR positif = besoin de financement court terme<br>
                        - BFR négatif = ressource excédentaire
                    </div>
                </div>
                
                <!-- Tableau SIG détaillé -->
                <h5 class="mt-4">📈 VOTRE TABLEAU SIG CALCULÉ AUTOMATIQUEMENT</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Libellé</th>
                                <th>Montant (FCFA)</th>
                                <th>Formule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Chiffre d'Affaires (CA)</td><td class="text-end fw-bold"><?= number_format($ca, 0, ',', ' ') ?></td><td><small>Comptes 70-79</small></td></tr>
                            <tr><td>Achats consommés</td><td class="text-end text-danger">- <?= number_format($achats, 0, ',', ' ') ?></td><td><small>Comptes 60-69</small></td></tr>
                            <tr class="bg-primary text-white"><td><strong>MARGE COMMERCIALE</strong></td><td class="text-end fw-bold"><?= number_format($marge_commerciale, 0, ',', ' ') ?></td><td><small>CA - Achats</small></td></tr>
                            <tr><td>Charges de personnel</td><td class="text-end text-danger">- <?= number_format($charges_personnel, 0, ',', ' ') ?></td><td><small>Comptes 661-669</small></td></tr>
                            <tr class="bg-success text-white"><td><strong>EXCÉDENT BRUT D'EXPLOITATION (EBE)</strong></td><td class="text-end fw-bold"><?= number_format($ebe, 0, ',', ' ') ?></td><td><small>Marge - Personnel</small></td></tr>
                            <tr><td>Autres charges d'exploitation</td><td class="text-end text-danger">- <?= number_format($autres_charges, 0, ',', ' ') ?></td><td><small>Classe 63-65</small></td></tr>
                            <tr class="bg-info text-white"><td><strong>RÉSULTAT D'EXPLOITATION</strong></td><td class="text-end fw-bold"><?= number_format($resultat_exploitation, 0, ',', ' ') ?></td><td><small>EBE - Autres charges</small></td></tr>
                            <tr class="bg-warning"><td><strong>CAPACITÉ D'AUTOFINANCEMENT (CAF)</strong></td><td class="text-end fw-bold"><?= number_format($capacite_autofinancement, 0, ',', ' ') ?></td><td><small>Résultat + Amortissements</small></td></tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Interprétation automatique -->
                <div class="alert alert-info mt-4">
                    <i class="bi bi-graph-up"></i>
                    <strong>📊 INTERPRÉTATION DE VOS INDICATEURS :</strong><br>
                    • <?php if($ebe > 0): ?>✅ EBE POSITIF : L'entreprise génère de la richesse (<?= number_format($ebe, 0, ',', ' ') ?> FCFA)<?php else: ?>⚠️ EBE NÉGATIF : L'exploitation ne couvre pas les charges personnelles<?php endif; ?><br>
                    • <?php if($capacite_autofinancement > 0): ?>💰 Capacité d'autofinancement positive : vous pouvez autofinancer vos projets<?php else: ?>📉 Capacité d'autofinancement négative : besoin de financement externe<?php endif; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="../sig.php" class="btn btn-primary">Voir le module SIG complet →</a>
                    <a href="../bilan.php" class="btn btn-success">Voir le module Bilan →</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../inc_footer.php'; ?>
