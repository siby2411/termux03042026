<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Soldes Intermédiaires de Gestion (SIG)";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// 1. Chiffre d'affaires (comptes 701, 703, etc.)
$ca = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();

// 2. Achats consommés (comptes 601, 606, etc.)
$achats = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();

// 3. Production de l'exercice (compte 72)
$production = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 720 AND 729")->fetchColumn();

// 4. Consommation en provenance des tiers (comptes 60,61,62)
$conso_tiers = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 629")->fetchColumn();

// 5. Charges de personnel (comptes 641, 651, 652, 653)
$charges_personnel = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (641,651,652,653)")->fetchColumn();

// 6. Autres charges d'exploitation (comptes 63-65)
$autres_charges = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 630 AND 659")->fetchColumn();

// 7. Produits financiers (comptes 75-76)
$produits_financiers = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 750 AND 769")->fetchColumn();

// 8. Charges financières (comptes 67)
$charges_financieres = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 670 AND 679")->fetchColumn();

// 9. Résultat exceptionnel (comptes 77 et 67)
$produits_exceptionnels = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 770 AND 779")->fetchColumn();
$charges_exceptionnelles = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 670 AND 679")->fetchColumn();

// Calculs
$marge_commerciale = $ca - $achats;
$valeur_ajoutee = $production + $marge_commerciale - $conso_tiers;
$ebe = $valeur_ajoutee - $charges_personnel;
$resultat_exploitation = $ebe - $autres_charges;
$resultat_financier = $produits_financiers - $charges_financieres;
$resultat_courant = $resultat_exploitation + $resultat_financier;
$resultat_exceptionnel = $produits_exceptionnels - $charges_exceptionnelles;
$resultat_net = $resultat_courant + $resultat_exceptionnel;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Soldes Intermédiaires de Gestion (SIG)</h5>
                <small>Analyse de la performance économique</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Poste</th><th>Montant (FCFA)</th><th>Calcul</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="fw-bold">Chiffre d'affaires (CA)</td><td class="text-end"><?= number_format($ca,0,',',' ') ?></td><td>Comptes 700-799</td></tr>
                            <tr><td class="fw-bold">Achats consommés</td><td class="text-end text-danger">- <?= number_format($achats,0,',',' ') ?></td><td>Comptes 600-699</td></tr>
                            <tr class="table-primary"><td class="fw-bold">MARGE COMMERCIALE</td><td class="text-end fw-bold"><?= number_format($marge_commerciale,0,',',' ') ?></td><td>CA - Achats</td></tr>
                            <tr><td class="fw-bold">Production de l'exercice</td><td class="text-end">+ <?= number_format($production,0,',',' ') ?></td><td>Compte 72</td></tr>
                            <tr><td class="fw-bold">Consommation tiers</td><td class="text-end text-danger">- <?= number_format($conso_tiers,0,',',' ') ?></td><td>Comptes 60-62</td></tr>
                            <tr class="table-info"><td class="fw-bold">VALEUR AJOUTÉE</td><td class="text-end fw-bold"><?= number_format($valeur_ajoutee,0,',',' ') ?></td><td>Production + Marge - Consommation</td></tr>
                            <tr><td class="fw-bold">Charges de personnel</td><td class="text-end text-danger">- <?= number_format($charges_personnel,0,',',' ') ?></td><td>Comptes 641,651,652,653</td></tr>
                            <tr class="table-success"><td class="fw-bold">EXCÉDENT BRUT D'EXPLOITATION (EBE)</td><td class="text-end fw-bold"><?= number_format($ebe,0,',',' ') ?></td><td>Valeur ajoutée - Personnel</td></tr>
                            <tr><td class="fw-bold">Autres charges d'exploitation</td><td class="text-end text-danger">- <?= number_format($autres_charges,0,',',' ') ?></td><td>Comptes 63-65</td></tr>
                            <tr class="table-warning"><td class="fw-bold">RÉSULTAT D'EXPLOITATION</td><td class="text-end fw-bold"><?= number_format($resultat_exploitation,0,',',' ') ?></td><td>EBE - Autres charges</td></tr>
                            <tr><td class="fw-bold">Produits financiers</td><td class="text-end">+ <?= number_format($produits_financiers,0,',',' ') ?></td><td>Comptes 75-76</td></tr>
                            <tr><td class="fw-bold">Charges financières</td><td class="text-end text-danger">- <?= number_format($charges_financieres,0,',',' ') ?></td><td>Comptes 67</td></tr>
                            <tr class="table-secondary"><td class="fw-bold">RÉSULTAT FINANCIER</td><td class="text-end fw-bold"><?= number_format($resultat_financier,0,',',' ') ?></td><td>Produits financiers - Charges financières</td></tr>
                            <tr class="table-dark"><td class="fw-bold">RÉSULTAT COURANT</td><td class="text-end fw-bold"><?= number_format($resultat_courant,0,',',' ') ?></td><td>Résultat exploitation + Résultat financier</td></tr>
                            <tr><td class="fw-bold">Produits exceptionnels</td><td class="text-end">+ <?= number_format($produits_exceptionnels,0,',',' ') ?></td><td>Comptes 77</td></tr>
                            <tr><td class="fw-bold">Charges exceptionnelles</td><td class="text-end text-danger">- <?= number_format($charges_exceptionnelles,0,',',' ') ?></td><td>Comptes 67 (exceptionnel)</td></tr>
                            <tr class="table-danger"><td class="fw-bold">RÉSULTAT EXCEPTIONNEL</td><td class="text-end fw-bold"><?= number_format($resultat_exceptionnel,0,',',' ') ?></td><td>Produits exceptionnels - Charges exceptionnelles</td></tr>
                            <tr class="table-primary fw-bold"><td class="fw-bold">RÉSULTAT NET DE L'EXERCICE</td><td class="text-end fw-bold"><?= number_format($resultat_net,0,',',' ') ?></td><td>Résultat courant + Résultat exceptionnel</td></tr>
                        </tbody>
                    <tr>
                </div>
                <div class="alert alert-info mt-3">
                    <strong>💡 Analyse :</strong>
                    <?php if ($ebe > 0): ?>
                        ✅ L'EBE est positif (<?= number_format($ebe,0,',',' ') ?> FCFA) : l'activité courante génère de la richesse.
                    <?php else: ?>
                        ⚠️ L'EBE est négatif (<?= number_format($ebe,0,',',' ') ?> FCFA) : l'exploitation ne couvre pas les charges de personnel.
                    <?php endif; ?>
                    <?php if ($resultat_net > 0): ?>
                        ✅ Le résultat net est bénéficiaire.
                    <?php elseif ($resultat_net < 0): ?>
                        ❌ Le résultat net est déficitaire.
                    <?php else: ?>
                        ➖ Le résultat net est nul.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
