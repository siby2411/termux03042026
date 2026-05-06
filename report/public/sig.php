<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Tableau SIG - Soldes Intermédiaires de Gestion";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Calcul des différents soldes SYSCOHADA
try {
    // 1. Chiffre d'Affaires (Comptes 70-79)
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 70 AND 79");
    $ca = $stmt->fetchColumn();
    
    // 2. Achats consommés (Comptes 60-69)
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 60 AND 69");
    $achats = $stmt->fetchColumn();
    
    // 3. Marge commerciale (CA - Achats consommés)
    $marge_commerciale = $ca - $achats;
    
    // 4. Production de l'exercice (Compte 72)
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 72");
    $production = $stmt->fetchColumn();
    
    // 5. Consommation en provenance des tiers (Comptes 60,61,62 sauf 601,602)
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (60,61,62)");
    $conso_tiers = $stmt->fetchColumn();
    
    // 6. Valeur Ajoutée (Production + Marge - Conso tiers)
    $valeur_ajoutee = ($production + $marge_commerciale) - $conso_tiers;
    
    // 7. Charges de personnel (Compte 66)
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 661 AND 669");
    $charges_personnel = $stmt->fetchColumn();
    
    // 8. Excédent Brut d'Exploitation (EBE)
    $ebe = $valeur_ajoutee - $charges_personnel;
    
    // 9. Autres charges/produits d'exploitation
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 63 AND 65");
    $autres_charges = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 73 AND 74");
    $autres_produits = $stmt->fetchColumn();
    
    // 10. Résultat d'Exploitation
    $resultat_exploitation = $ebe - $autres_charges + $autres_produits;
    
    // 11. Résultat Financier
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 67 AND 68");
    $charges_financieres = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 75 AND 76");
    $produits_financiers = $stmt->fetchColumn();
    $resultat_financier = $produits_financiers - $charges_financieres;
    
    // 12. Résultat Courant (Exploitation + Financier)
    $resultat_courant = $resultat_exploitation + $resultat_financier;
    
    // 13. Résultat Exceptionnel
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 67");
    $charges_exceptionnelles = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 77");
    $produits_exceptionnels = $stmt->fetchColumn();
    $resultat_exceptionnel = $produits_exceptionnels - $charges_exceptionnelles;
    
    // 14. Résultat Net
    $resultat_net = $resultat_courant + $resultat_exceptionnel;
    
} catch (PDOException $e) {
    $error = "Erreur de calcul : " . $e->getMessage();
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Tableau des Soldes Intermédiaires de Gestion (SIG)</h5>
                <small class="text-muted">Norme SYSCOHADA - UEMOA | Exercice <?= date('Y') ?></small>
            </div>
            <div class="card-body p-0">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger m-3"><?= $error ?></div>
                <?php endif; ?>
                
                <table class="table table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60%">Libellé</th>
                            <th style="width: 20%">Montant (FCFA)</th>
                            <th style="width: 20%">Soldes (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Chiffre d'Affaires -->
                        <tr class="table-light">
                            <td><strong>Chiffre d'Affaires (70-79)</strong></td>
                            <td class="text-end"><?= number_format($ca, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Achats consommés (60-69)</td>
                            <td class="text-end"><?= number_format($achats, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-primary text-white">
                            <td><strong>= MARGE COMMERCIALE</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($marge_commerciale, 0, ',', ' ') ?></td>
                        </tr>
                        <tr>
                            <td><strong>+ Production de l'exercice (72)</strong></td>
                            <td class="text-end"><?= number_format($production, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Consommation en provenance des tiers</td>
                            <td class="text-end"><?= number_format($conso_tiers, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-info text-white">
                            <td><strong>= VALEUR AJOUTÉE</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($valeur_ajoutee, 0, ',', ' ') ?></td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Charges de personnel (66)</td>
                            <td class="text-end"><?= number_format($charges_personnel, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-success text-white">
                            <td><strong>= EXCÉDENT BRUT D'EXPLOITATION (EBE)</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold <?= $ebe >= 0 ? 'text-white' : 'text-danger' ?>">
                                <?= number_format($ebe, 0, ',', ' ') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4">+ Autres produits d'exploitation (73-74)</td>
                            <td class="text-end"><?= number_format($autres_produits, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Autres charges d'exploitation (63-65)</td>
                            <td class="text-end"><?= number_format($autres_charges, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-warning">
                            <td><strong>= RÉSULTAT D'EXPLOITATION</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($resultat_exploitation, 0, ',', ' ') ?></td>
                        </tr>
                        <tr>
                            <td><strong>+ Produits financiers (75-76)</strong></td>
                            <td class="text-end"><?= number_format($produits_financiers, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Charges financières (67-68)</td>
                            <td class="text-end"><?= number_format($charges_financieres, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-warning">
                            <td><strong>= RÉSULTAT FINANCIER</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($resultat_financier, 0, ',', ' ') ?></td>
                        </tr>
                        <tr class="bg-secondary text-white">
                            <td><strong>= RÉSULTAT COURANT</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($resultat_courant, 0, ',', ' ') ?></td>
                        </tr>
                        <tr>
                            <td><strong>+ Produits exceptionnels (77)</strong></td>
                            <td class="text-end"><?= number_format($produits_exceptionnels, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr>
                            <td class="ps-4">- Charges exceptionnelles (67)</td>
                            <td class="text-end"><?= number_format($charges_exceptionnelles, 0, ',', ' ') ?></td>
                            <td class="text-end">-</td>
                        </tr>
                        <tr class="bg-secondary text-white">
                            <td><strong>= RÉSULTAT EXCEPTIONNEL</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($resultat_exceptionnel, 0, ',', ' ') ?></td>
                        </tr>
                        <tr class="table-dark">
                            <td><strong>= RÉSULTAT NET DE L'EXERCICE</strong></td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold fs-5 <?= $resultat_net >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($resultat_net, 0, ',', ' ') ?> F
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Indicateurs de performance -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Taux de marge commerciale</h6>
                        <h4 class="<?= ($ca > 0 && ($marge_commerciale/$ca)*100 >= 0) ? 'text-success' : 'text-danger' ?>">
                            <?= $ca > 0 ? number_format(($marge_commerciale / $ca) * 100, 2) : 0 ?>%
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Taux de valeur ajoutée / CA</h6>
                        <h4 class="<?= ($ca > 0 && ($valeur_ajoutee/$ca)*100 >= 30) ? 'text-success' : 'text-warning' ?>">
                            <?= $ca > 0 ? number_format(($valeur_ajoutee / $ca) * 100, 2) : 0 ?>%
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Rentabilité nette</h6>
                        <h4 class="<?= $resultat_net >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($resultat_net, 0, ',', ' ') ?> F
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Interprétation SIG -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Interprétation du SIG SYSCOHADA :</strong><br>
                    • <strong>EBE :</strong> <?= $ebe >= 0 ? "Positif (" . number_format($ebe, 0, ',', ' ') . " FCFA) - L'entreprise génère de la richesse." : "Négatif - L'exploitation ne couvre pas les charges." ?><br>
                    • <strong>Résultat net :</strong> <?= $resultat_net >= 0 ? "Bénéfice de " . number_format($resultat_net, 0, ',', ' ') . " FCFA" : "Perte de " . number_format(abs($resultat_net), 0, ',', ' ') . " FCFA" ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
